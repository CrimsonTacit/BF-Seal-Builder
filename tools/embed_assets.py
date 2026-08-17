#!/usr/bin/env python3
"""Re-embed binary assets into index.html.

Run after changing the font or any emblem in TFEmblems/:

    python3 tools/embed_assets.py            # emblems only (stdlib)
    python3 tools/embed_assets.py --font     # also rebuild the embedded font (needs fontTools+brotli)

The app is a single self-contained file, so the Microstyle font and the
task-force emblem PNGs live inside index.html as base64 constants.
"""
import base64
import json
import re
import struct
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
INDEX = ROOT / "index.html"
EMBLEM_DIR = ROOT / "TFEmblems"
FONT_SRC = Path.home() / "Library/Fonts/microsbe.ttf"

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
            "b64": "data:image/png;base64," + base64.b64encode(data).decode(),
        }
    return "const CHARGES = " + json.dumps(charges) + ";"


def build_font_b64() -> str:
    # The 1991 Agfa TTF is rejected by browser font sanitizers (OTS):
    # usWeightClass uses the legacy 1-9 scale and the PCLT/kern tables are
    # malformed. Repair + convert to WOFF2 before embedding.
    from io import BytesIO
    from fontTools.ttLib import TTFont
    f = TTFont(FONT_SRC)
    f["OS/2"].usWeightClass = 700
    f["OS/2"].fsSelection = 0x40
    f["head"].macStyle = 0
    for t in ("PCLT", "kern"):
        if t in f:
            del f[t]
    f.flavor = "woff2"
    buf = BytesIO()
    f.save(buf)
    return base64.b64encode(buf.getvalue()).decode()


def main():
    html = INDEX.read_text()

    html, n = re.subn(
        r"/\*CHARGES_START\*/.*?/\*CHARGES_END\*/",
        "/*CHARGES_START*/" + build_charges() + "/*CHARGES_END*/",
        html, flags=re.S,
    )
    assert n == 1, "CHARGES markers not found"
    print(f"embedded {len(list(EMBLEM_DIR.glob('*.png')))} emblems")

    if "--font" in sys.argv:
        b64 = build_font_b64()
        html, n = re.subn(r'const FONT_B64 = "[^"]*";', f'const FONT_B64 = "{b64}";', html)
        assert n == 1, "FONT_B64 not found"
        print(f"embedded font ({len(b64)} chars)")

    INDEX.write_text(html)
    print("wrote", INDEX)


if __name__ == "__main__":
    main()
