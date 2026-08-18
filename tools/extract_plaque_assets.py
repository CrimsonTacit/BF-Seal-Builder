#!/usr/bin/env python3
"""Extract the plaque art from examples/Plaques/NewPlaqueDesign copy.psd.

    ~/.venv/bin/python3 tools/extract_plaque_assets.py

Needs psd-tools + scikit-image + Pillow (install with
`pip install 'psd-tools[composite]'`), so it is NOT part of the normal
build — run it only when the source PSD changes and commit the results.

The PSD carries every part in every colourway (12 backings, 7 border
materials, 8 text materials). Every colourway of a given part turns out to be
the *same* grayscale art run through a different colour ramp, so instead of
shipping dozens of full-size PNGs we ship one grayscale PNG per part plus a
small colour table
per variant. The tool re-applies the ramp at render time with an SVG
<feComponentTransfer type="table">, which reproduces the original pixels and
also lets a user pick a colour the PSD never had. Each table is checked by
replaying it over the grayscale base and diffing against the variant the PSD
actually draws; everything here lands within a few counts of 255.

The metal parts come out as four *independently colourable* pieces, because
that is how the PSD builds them: `Border/<variant>` holds `Bolts` over the
rails, and `Badge` holds a `Bars` group and a `Delta` group with their own
variant stacks. Each piece ships twice — a polished base and a matte one —
since the two finishes bevel in opposite directions (measured: polished
variants correlate ~0.98+ with each other, matte ones anti-correlate), and
one ramp table cannot serve both.

Writes:
    assets/plaque/plate.png        grayscale + alpha, the plate/backing
    assets/plaque/frame.png        + frame-matte.png   the frame rails
    assets/plaque/bolts.png        + bolts-matte.png   the corner bolts
    assets/plaque/delta.png        + delta-matte.png   the 2399 badge delta
    assets/plaque/bars.png         + bars-matte.png    the badge bars
    assets/plaque/ramps.json       colour tables for every variant + text ramps
"""
import json
from pathlib import Path

import numpy as np
from PIL import Image
from psd_tools import PSDImage

ROOT = Path(__file__).resolve().parent.parent
PSD_PATH = ROOT / "examples" / "Plaques" / "NewPlaqueDesign copy.psd"
OUT = ROOT / "assets" / "plaque"
STEPS = 33  # table resolution for the gradient maps

# Colourway ids as the tool knows them, mapped to each part's PSD layer names.
# The polished and matte sets are extracted separately (opposite bevels).
POLISHED = {
    "border": {"gold": "Gold Border Shiny", "silver": "Silver Border Shiny", "blue": "Blue Border"},
    "delta": {"gold": "Gold Delta Shiny", "silver": "Silver Delta Shiny", "blue": "Blue Delta"},
    # the PSD has no blue bars; that one ramp is borrowed from the delta below
    "bars": {"gold": "Gold Bars Shiny", "silver": "Silver Bars Shiny"},
}
MATTE = {
    "border": {"goldMatte": "Gold Border", "silverMatte": "Silver Border",
               "steel": "Steel Border", "black": "Black Border"},
    "delta": {"goldMatte": "Gold Delta", "silverMatte": "Silver Delta",
              "steel": "Steel Delta", "black": "Black Delta"},
    "bars": {"goldMatte": "Gold Bars", "silverMatte": "Silver Bars",
             "steel": "Steel Bars", "black": "Black Bars"},
}


def find(group, name):
    for layer in group:
        if layer.name == name:
            return layer
    raise KeyError(name)


def solo(group, name):
    """Show only `name` inside `group` and composite the group."""
    for layer in group:
        layer.visible = layer.name == name
    return group


def as_rgba(layer, psd):
    return np.asarray(layer.composite(viewport=psd.viewbox).convert("RGBA"), dtype=float)


def gray_and_alpha(rgba):
    return rgba[:, :, :3].mean(2), rgba[:, :, 3]


def ramp_from(lum, alpha, rgba, lo, hi):
    """Sample the luminance -> RGB mapping of a variant as STEPS table rows."""
    mask = alpha > 250
    idx = np.clip(np.round((lum - lo) / (hi - lo) * (STEPS - 1)).astype(int), 0, STEPS - 1)
    table = np.zeros((STEPS, 3))
    known = np.zeros(STEPS, dtype=bool)
    for i in range(STEPS):
        sel = mask & (idx == i)
        if sel.sum() >= 8:
            table[i] = rgba[:, :, :3][sel].mean(0)
            known[i] = True
    # fill unsampled ends/gaps by interpolating the bins we did see
    xs = np.arange(STEPS)
    for c in range(3):
        table[:, c] = np.interp(xs, xs[known], table[known, c])
    return [[round(v / 255, 5) for v in row] for row in table]


def apply_ramp(lum, lo, hi, table):
    """Render a ramp table over a part's own luminance — the tool's feComponentTransfer."""
    t = np.clip((lum - lo) / (hi - lo), 0, 1)
    xs = np.linspace(0, 1, STEPS)
    arr = np.array(table)
    return np.dstack([np.interp(t, xs, arr[:, c]) * 255 for c in range(3)])


def part(psd, render, variants, default, out_name, results_key, results):
    """Write one grayscale PNG for a part + a colour table per variant.

    `render(variant_name)` composites the part in that colourway; `default`
    is the variant the grayscale base is normalised from.
    """
    base = render(default)
    lum, alpha = gray_and_alpha(base)
    inside = alpha > 250
    lo, hi = float(lum[inside].min()), float(lum[inside].max())

    norm = np.clip((lum - lo) / (hi - lo), 0, 1) * 255
    img = Image.fromarray(
        np.dstack([norm.astype(np.uint8), alpha.astype(np.uint8)]), mode="LA"
    )
    OUT.mkdir(parents=True, exist_ok=True)
    img.save(OUT / out_name, optimize=True)
    print(f"  {out_name}: {img.size} {(OUT / out_name).stat().st_size // 1024} KB")

    # What matters is not whether a variant *correlates* with the base but
    # whether its table, replayed over the base, lands back on its own pixels:
    # the low-contrast mattes correlate poorly (their range is a few dozen
    # counts, so antialiasing dominates) yet still reconstruct to ~1/255.
    ramps = {}
    for vid, vname in variants.items():
        rgba = render(vname)
        ramps[vid] = ramp_from(lum, alpha, rgba, lo, hi)
        err = np.abs(apply_ramp(lum, lo, hi, ramps[vid])[inside]
                     - rgba[:, :, :3][inside]).mean()
        flag = "!" if err > 8 else " "
        print(f"    {flag} {vname}: reconstructs to {err:.1f}/255")
    results[results_key] = ramps
    return {"lum": lum, "alpha": alpha, "lo": lo, "hi": hi, "render": render}


def borrow(dst, dst_key, results, vid, donor_key, check_vid, check_layer):
    """Copy a colourway the PSD never drew for this part off a sibling part.

    Both pieces are the same metal in every colourway that *does* exist, and
    both tables are indexed by position within their own part's dynamic range,
    so the donor's table transfers directly. The printed check re-renders a
    colourway both parts do have, through the donor's table, to show what that
    transfer costs in raw pixel counts.
    """
    results[dst_key][vid] = list(results[donor_key][vid])
    got = apply_ramp(dst["lum"], dst["lo"], dst["hi"], results[donor_key][check_vid])
    want = dst["render"](check_layer)[:, :, :3]
    m = dst["alpha"] > 250
    err = np.abs(got[m] - want[m]).mean()
    print(f"    borrowed {vid!r} from {donor_key} "
          f"(cross-check on {check_vid!r}: mean error {err:.1f}/255)")


def text_ramps(psd, text_group, variants):
    """Vertical colour ramp of each text material, sampled off the ship name."""
    out = {}
    for vid, vname in variants.items():
        grp = solo(text_group, vname)
        rgba = as_rgba(grp, psd)
        alpha = rgba[:, :, 3]
        rows = np.where((alpha > 250).sum(1) > 40)[0]
        if rows.size == 0:
            print(f"    ! {vname}: no solid pixels found")
            continue
        # the ship-name row is the tallest run of solid scanlines
        runs, start = [], rows[0]
        for a, b in zip(rows, rows[1:]):
            if b - a > 3:
                runs.append((start, a))
                start = b
        runs.append((start, rows[-1]))
        y0, y1 = max(runs, key=lambda r: r[1] - r[0])
        stops = []
        for i in range(9):
            y = int(round(y0 + (y1 - y0) * i / 8))
            band = rgba[max(y0, y - 1):min(y1 + 1, y + 2)]
            sel = band[:, :, 3] > 250
            if sel.sum() < 10:
                continue
            rgb = band[:, :, :3][sel].mean(0)
            stops.append([round(i / 8, 4), "#%02x%02x%02x" % tuple(int(round(v)) for v in rgb)])
        out[vid] = stops
    return out


def main():
    psd = PSDImage.open(PSD_PATH)
    color = find(psd, "Color")
    results = {}

    print("plate:")
    backing = find(color, "Backing")
    part(psd, lambda n: as_rgba(solo(backing, n), psd), {
        "blue": "Blue Backing", "steel": "Steel Backing", "silver": "Silver Backing",
        "gold": "Gold Backing", "bronze": "Bronze Backing", "copper": "Copper Backing",
        "emerald": "Emerald Backing", "ruby": "Ruby Backing", "cyan": "Green-Cyan Backing",
        "cobalt": "Cobalt Backing", "amethyst": "Amethyst Backing", "black": "Black Backing",
    }, "Blue Backing", "plate.png", "plate", results)

    # Each border colourway is its own group holding the bolts over the rails;
    # solo the colourway, then solo the piece inside it.
    border = find(color, "Border")

    def border_piece(bolts):
        def render(vname):
            for v in border:
                v.visible = v.name == vname
            g = find(border, vname)
            for layer in g:
                layer.visible = (layer.name == "Bolts") == bolts
            return as_rgba(g, psd)
        return render

    badge = find(color, "Badge")
    bars_group, delta_group = find(badge, "Bars"), find(badge, "Delta")

    def group_piece(group):
        return lambda vname: as_rgba(solo(group, vname), psd)

    pieces = [
        ("frame", border_piece(False), "border", "Gold Border Shiny", "Gold Border"),
        ("bolts", border_piece(True), "border", "Gold Border Shiny", "Gold Border"),
        ("delta", group_piece(delta_group), "delta", "Gold Delta Shiny", "Gold Delta"),
        ("bars", group_piece(bars_group), "bars", "Gold Bars Shiny", "Gold Bars"),
    ]
    for key, render, vkey, shiny_default, matte_default in pieces:
        print(f"{key} (polished):")
        info = part(psd, render, POLISHED[vkey], shiny_default,
                    f"{key}.png", key, results)
        if key == "bars":
            borrow(info, key, results, "blue", "delta", "gold", "Gold Bars Shiny")
        print(f"{key} (matte):")
        part(psd, render, MATTE[vkey], matte_default,
             f"{key}-matte.png", key + "Matte", results)

    print("text ramps:")
    results["text"] = text_ramps(psd, find(color, "Text"), {
        "gold": "Gold Shiny", "goldMatte": "Gold Text",
        "silver": "Silver Text Shiny", "silverMatte": "Silver Text",
        "steel": "Steel Text", "blue": "Blue Text",
        "black": "Black Text", "blackShiny": "Black Shiny",
    })
    for k, v in results["text"].items():
        print(f"  {k}: {len(v)} stops")

    (OUT / "ramps.json").write_text(json.dumps(results, indent=1))
    print("wrote", OUT / "ramps.json")


if __name__ == "__main__":
    main()
