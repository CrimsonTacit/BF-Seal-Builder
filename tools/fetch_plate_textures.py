#!/usr/bin/env python3
"""Fetch the plaque's wood/stone/powder-coat backing photos from ambientCG.

    python3 tools/fetch_plate_textures.py

Not part of the normal build — run only when the finish list below changes.
Downloads ~15-25MB per texture from ambientcg.com (CC0 licence — no
attribution required, https://ambientcg.com/a/<AssetId>) and writes a
cropped JPEG per finish straight into assets/plaque/. These carry no alpha
of their own — plaque-tool.html clips them to the plate's silhouette at
render time via an SVG feImage pulling plate.png's alpha, so the shape lives
in one place. Unlike the metal backings (extract_plaque_assets.py), these
aren't grayscale + colour-ramp: they're real photos with their own baked-in
colour, so each finish is a fixed swatch rather than something a user
recolours.
"""
import io
import urllib.request
import zipfile
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / "assets" / "plaque"
W, H = 2350, 1700
JPEG_QUALITY = 88

# ambientCG asset IDs (CC0) picked for the plaque's finish swatches.
TEXTURES = {
    "tex-wood-walnut.jpg": "Wood051",
    "tex-wood-oak.jpg": "Wood095",
    "tex-marble-gray.jpg": "Marble012",
    "tex-marble-cream.jpg": "Marble020",
    "tex-granite-gray.jpg": "Granite002A",
    "tex-granite-brown.jpg": "Granite004B",
    "tex-powder-navy.jpg": "Metal027",
}


def fetch_color_jpg(asset_id):
    url = f"https://ambientcg.com/get?file={asset_id}_2K-JPG.zip"
    print(f"  downloading {url}")
    req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0"})
    with urllib.request.urlopen(req) as resp:
        data = resp.read()
    zf = zipfile.ZipFile(io.BytesIO(data))
    color_name = next(n for n in zf.namelist() if "color" in n.lower())
    return Image.open(io.BytesIO(zf.read(color_name))).convert("RGB")


def cover_fit(im, w, h):
    """Resize+crop like CSS background-size:cover, centred."""
    iw, ih = im.size
    scale = max(w / iw, h / ih)
    nw, nh = round(iw * scale), round(ih * scale)
    im = im.resize((nw, nh), Image.LANCZOS)
    x0, y0 = (nw - w) // 2, (nh - h) // 2
    return im.crop((x0, y0, x0 + w, y0 + h))


def main():
    for out_name, asset_id in TEXTURES.items():
        print(f"{out_name} <- {asset_id}")
        im = cover_fit(fetch_color_jpg(asset_id), W, H)
        im.save(OUT / out_name, quality=JPEG_QUALITY, optimize=True)
        print(f"  wrote {out_name} ({(OUT / out_name).stat().st_size // 1024} KB)")


if __name__ == "__main__":
    main()
