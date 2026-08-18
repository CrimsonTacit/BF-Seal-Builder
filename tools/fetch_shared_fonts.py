#!/usr/bin/env python3
"""Download the loose webfonts every tool loads by URL.

    python3 tools/fetch_shared_fonts.py

Downloads every loose webfont (all SIL OFL 1.1) from Google Fonts into
fonts/webfonts/, cached so rebuilds work offline, and checks that
fonts/sealstile.woff2 is present.

Sealstile used to be extracted here out of seal-tool.html's FONT_B64 constant.
Nothing is base64-embedded any more, and the built woff2 is committed, so this
only verifies the file exists — rebuild it with `embed_assets.py --font`
(which needs a fonttools venv) if it is ever missing.

This is the single source of truth for fonts/webfonts/. embed_header_assets.py
downloads four of the same faces on its own and caches into the same
directory, so the two agree by construction.

Stdlib only.
"""
import re
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
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
    seal = FONTS / "sealstile.woff2"
    if seal.exists():
        print(f"present {seal} ({seal.stat().st_size} bytes)")
    else:
        print(f"MISSING {seal} -- rebuild with: python3 tools/embed_assets.py --font")

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
