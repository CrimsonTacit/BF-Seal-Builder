#!/usr/bin/env python3
"""Refresh seal-tool.html's asset registry, and rebuild the Sealstile font.

Run after changing the font or any emblem in assets/emblems/:

    python3 tools/embed_assets.py            # emblem registry only (stdlib)
    python3 tools/embed_assets.py --font     # also rebuild the font (needs fontTools+brotli)

Nothing is base64-embedded any more. The tools are served over HTTP, so the
emblems and the font are fetched by URL and inlined as data URIs only at
export time (an SVG rasterised through an <img> can't fetch). This script
therefore writes the CHARGES *registry* — names, pixel sizes and paths — plus
the built font at fonts/sealstile.woff2.
"""
import re
import struct
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
INDEX = ROOT / "seal-tool.html"
EMBLEM_DIR = ROOT / "assets" / "emblems"
FONT_SRC = ROOT / "fonts/LibrestileExtBold.ttf"

EMBLEM_NAMES = {
    "tf17b": "Task Force 17", "tf21b": "Task Force 21", "tf47b": "Task Force 47",
    "tf72b": "Task Force 72", "tf86b": "Task Force 86", "tf93b": "Task Force 93",
}


def png_size(data: bytes):
    w, h = struct.unpack(">II", data[16:24])
    return w, h


def build_charges() -> str:
    charges = {}
    for p in sorted(EMBLEM_DIR.glob("*.png")):
        data = p.read_bytes()
        w, h = png_size(data)
        key = p.stem
        charges[key] = {
            "name": EMBLEM_NAMES.get(key, key.upper()),
            "w": w, "h": h,
            "url": f"assets/emblems/{key}.png",
        }
    rows = ",\n".join(
        f'  "{k}": {{name:"{v["name"]}", w:{v["w"]}, h:{v["h"]}, url:"{v["url"]}"}}'
        for k, v in charges.items()
    )
    return "const CHARGES = {\n" + rows + "\n};"


def build_font_woff2() -> bytes:
    # "Sealstile" = Librestile Ext Bold (OFL 1.1, fonts/OFL-Librestile.txt) with
    # a bullet glyph added and cmap aliases for en dash / right quote. Librestile
    # ships no U+2022, but the seal text convention uses "•" as a separator.
    # "Librestile" is an OFL Reserved Font Name, so the modified font must be
    # renamed — hence Sealstile in the name table and in index.html's CSS.
    import math
    from io import BytesIO
    from fontTools.ttLib import TTFont
    from fontTools.pens.ttGlyphPen import TTGlyphPen

    f = TTFont(FONT_SRC)
    cmap_tables = [t for t in f["cmap"].tables if t.isUnicode()]
    cmap = f.getBestCmap()

    # Bullet: circle matching Microstyle's proportions relative to cap height
    # (diameter 0.84 x cap, center 0.60 x cap above baseline, bearings 0.19 x cap).
    cap = f["OS/2"].sCapHeight  # 771
    r = round(0.42 * cap)
    cx, cy = round(0.19 * cap) + r, round(0.60 * cap)
    adv = 2 * cx
    pen = TTGlyphPen(None)
    R = r / math.cos(math.pi / 8)  # control-point radius for 8 quadratic arcs
    pen.moveTo((cx + r, cy))
    for i in range(8):
        ac = math.radians(45 * i + 22.5)
        ap = math.radians(45 * (i + 1))
        pen.qCurveTo((round(cx + R * math.cos(ac)), round(cy + R * math.sin(ac))),
                     (round(cx + r * math.cos(ap)), round(cy + r * math.sin(ap))))
    pen.closePath()
    f.setGlyphOrder(f.getGlyphOrder() + ["bullet"])
    f["glyf"]["bullet"] = pen.glyph()
    f["hmtx"]["bullet"] = (adv, cx - r)
    for t in cmap_tables:
        t.cmap[0x2022] = "bullet"
        t.cmap[0x2013] = cmap[0x2D]    # en dash -> hyphen
        t.cmap[0x2019] = cmap[0x27]    # right single quote -> apostrophe

    # Rename away from the reserved name, keeping the upstream credit.
    name = f["name"]
    for rec in list(name.names):
        if rec.nameID in (1, 3, 4, 6, 16):
            s = {1: "Sealstile", 16: "Sealstile", 4: "Sealstile Bold",
                 3: "Sealstile-Bold", 6: "Sealstile-Bold"}[rec.nameID]
            name.setName(s, rec.nameID, rec.platformID, rec.platEncID, rec.langID)
        elif rec.nameID == 0:
            name.setName(rec.toUnicode() + " Modified version (Sealstile): bullet glyph added.",
                         0, rec.platformID, rec.platEncID, rec.langID)

    f.flavor = "woff2"
    buf = BytesIO()
    f.save(buf)
    return buf.getvalue()


def main():
    html = INDEX.read_text()

    html, n = re.subn(
        r"/\*CHARGES_START\*/.*?/\*CHARGES_END\*/",
        "/*CHARGES_START*/" + build_charges() + "/*CHARGES_END*/",
        html, flags=re.S,
    )
    assert n == 1, "CHARGES markers not found"
    print(f"registered {len(list(EMBLEM_DIR.glob('*.png')))} emblems")

    if "--font" in sys.argv:
        # every tool loads this by URL; nothing embeds it
        loose = INDEX.parent / "fonts" / "sealstile.woff2"
        loose.write_bytes(build_font_woff2())
        print(f"wrote {loose} ({loose.stat().st_size} bytes)")

    INDEX.write_text(html)
    print("wrote", INDEX)


if __name__ == "__main__":
    main()
