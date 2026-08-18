#!/usr/bin/env python3
"""Extract the plaque art from examples/Plaques/NewPlaqueDesign copy.psd.

    ~/.venv/bin/python3 tools/extract_plaque_assets.py

Needs psd-tools + scikit-image + Pillow (install with
`pip install 'psd-tools[composite]'`), so it is NOT part of the normal
build — run it only when the source PSD changes and commit the results.

The PSD carries every part in every colourway (12 backings, 7 border
materials, 8 text materials). Every colourway of a given part turns out to be
the *same* grayscale art run through a different colour ramp — verified here
by correlating luminance between variants — so instead of shipping dozens of
full-size PNGs we ship one grayscale PNG per part plus a small colour table
per variant. The tool re-applies the ramp at render time with an SVG
<feComponentTransfer type="table">, which reproduces the original pixels and
also lets a user pick a colour the PSD never had.

Writes:
    assets/plaque/plate.png    grayscale + alpha, the plate/backing
    assets/plaque/border.png   grayscale + alpha, frame rails + corner bolts
    assets/plaque/badge.png    grayscale + alpha, 2399 delta + bars
    assets/plaque/ramps.json   colour tables for every variant + text ramps
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


def part(psd, group, variants, default, out_name, results_key, results):
    """Write one grayscale PNG for `group` + a colour table per variant."""
    base = as_rgba(solo(group, default), psd) if variants else as_rgba(group, psd)
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

    ramps = {}
    for vid, vname in variants.items():
        rgba = as_rgba(solo(group, vname), psd)
        vlum, valpha = gray_and_alpha(rgba)
        corr = np.corrcoef(lum[inside].ravel(), vlum[inside].ravel())[0, 1]
        if corr < 0.97:
            print(f"    ! {vname}: luminance correlation {corr:.3f} — not a pure recolour")
        ramps[vid] = ramp_from(lum, alpha, rgba, lo, hi)
    if not variants:
        ramps["default"] = ramp_from(lum, alpha, base, lo, hi)
    results[results_key] = ramps


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
    part(psd, find(color, "Backing"), {
        "blue": "Blue Backing", "steel": "Steel Backing", "silver": "Silver Backing",
        "gold": "Gold Backing", "bronze": "Bronze Backing", "copper": "Copper Backing",
        "emerald": "Emerald Backing", "ruby": "Ruby Backing", "cyan": "Green-Cyan Backing",
        "cobalt": "Cobalt Backing", "amethyst": "Amethyst Backing", "black": "Black Backing",
    }, "Blue Backing", "plate.png", "plate", results)

    # The border ships in two finishes whose bevels run opposite ways (a
    # polished rail lit from above vs a matte one), so they need separate
    # grayscale bases — one ramp table cannot serve both.
    print("border (polished):")
    part(psd, find(color, "Border"), {
        "gold": "Gold Border Shiny", "silver": "Silver Border Shiny", "blue": "Blue Border",
    }, "Gold Border Shiny", "border.png", "border", results)

    print("border (matte):")
    part(psd, find(color, "Border"), {
        "goldMatte": "Gold Border", "silverMatte": "Silver Border",
        "steel": "Steel Border", "black": "Black Border",
    }, "Gold Border", "border-matte.png", "borderMatte", results)

    print("badge:")
    part(psd, find(color, "Badge"), {}, None, "badge.png", "badge", results)

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
