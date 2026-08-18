#!/usr/bin/env python3
"""Fetch/derive the loose font files used by the banner, plaque and mission tools.

    python3 tools/fetch_shared_fonts.py

Unlike the seal/header tools (single-file, fonts embedded as base64), these
tools load fonts as plain files. This script:

  * extracts fonts/sealstile.woff2 from the FONT_B64 constant already embedded
    in seal-tool.html (no fonttools venv needed; embed_assets.py --font also
    writes this file whenever the font is regenerated), and
  * downloads every loose webfont (all SIL OFL 1.1) from Google Fonts into
    fonts/webfonts/, cached so rebuilds work offline.

This is the single source of truth for fonts/webfonts/. embed_header_assets.py
downloads four of the same faces on its own (it needs them as base64, not as
files) and caches into the same directory, so the two agree by construction.

Stdlib only.
"""
import base64
import re
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SEAL = ROOT / "seal-tool.html"
FONTS = ROOT / "fonts"
CACHE = FONTS / "webfonts"

UA = ("Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 "
      "(KHTML, like Gecko) Chrome/126.0 Safari/537.36")

# filename stem -> Google Fonts css2 spec (latin subset is extracted)
WEBFONTS = {
    # banner + plaque: the AGaramondPro stand-ins
    "ebgaramond-500":   "EB+Garamond:wght@500",
    "ebgaramond-600":   "EB+Garamond:wght@600",
    "ebgaramond-it500": "EB+Garamond:ital,wght@1,500",
    # also embedded by embed_header_assets.py; the mission tool loads them loose
    "tenor":            "Tenor+Sans",
    "cinzel":           "Cinzel:wght@600",
    "michroma":         "Michroma",
    "orbitron":         "Orbitron:wght@600",
    # mission tool only: poster/book-cover display faces
    "cinzeldecor-700":  "Cinzel+Decorative:wght@700",
    "playfair-700":     "Playfair+Display:wght@700",
    "cormorant-600":    "Cormorant+Garamond:wght@600",
    "bebas":            "Bebas+Neue",
    "oswald-500":       "Oswald:wght@500",
    "anton":            "Anton",
}


def fetch(url: str) -> bytes:
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    return urllib.request.urlopen(req, timeout=30).read()


def latin_woff2(spec: str) -> bytes:
    css = fetch(f"https://fonts.googleapis.com/css2?family={spec}&display=swap").decode()
    urls = re.findall(r"/\* latin \*/.*?url\((\S+?)\) format\('woff2'\)", css, re.S)
    assert urls, f"no latin woff2 found for {spec}"
    return fetch(urls[-1])


def main():
    b64 = re.search(r'const FONT_B64 = "([^"]*)"', SEAL.read_text()).group(1)
    seal = FONTS / "sealstile.woff2"
    seal.write_bytes(base64.b64decode(b64))
    print("wrote", seal)

    CACHE.mkdir(parents=True, exist_ok=True)
    for stem, spec in WEBFONTS.items():
        p = CACHE / f"{stem}.woff2"
        if not p.exists():
            p.write_bytes(latin_woff2(spec))
            print("downloaded", p)
        else:
            print("cached", p)


if __name__ == "__main__":
    main()
