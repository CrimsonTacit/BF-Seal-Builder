#!/usr/bin/env python3
"""Embed assets into header-tool.html (the BFMS header/wordmark builder).

    python3 tools/embed_header_assets.py

Reuses the Sealstile font and TF emblem CHARGES already embedded in
seal-tool.html (run tools/embed_assets.py first if those changed), and embeds
four OFL-licensed webfonts downloaded from Google Fonts. Downloads are
cached in fonts/webfonts/ so rebuilds work offline.
"""
import base64
import json
import re
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
HEADER = ROOT / "header-tool.html"
INDEX = ROOT / "seal-tool.html"
CACHE = ROOT / "fonts" / "webfonts"

UA = ("Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 "
      "(KHTML, like Gecko) Chrome/126.0 Safari/537.36")

# id, display label, css family name, Google Fonts css2 spec, weight
WEBFONTS = [
    ("tenor",    "Tenor Sans",  "Tenor Sans", "Tenor+Sans",        400),
    ("cinzel",   "Cinzel",      "Cinzel",     "Cinzel:wght@600",   600),
    ("michroma", "Michroma",    "Michroma",   "Michroma",          400),
    ("orbitron", "Orbitron",    "Orbitron",   "Orbitron:wght@600", 600),
]


def fetch(url: str) -> bytes:
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    return urllib.request.urlopen(req, timeout=30).read()


def latin_woff2(spec: str) -> bytes:
    css = fetch(f"https://fonts.googleapis.com/css2?family={spec}&display=swap").decode()
    urls = re.findall(r"/\* latin \*/.*?url\((\S+?)\) format\('woff2'\)", css, re.S)
    assert urls, f"no latin woff2 found for {spec}"
    return fetch(urls[-1])


def main():
    html = HEADER.read_text()
    idx = INDEX.read_text()

    seal_b64 = re.search(r'const FONT_B64 = "([^"]*)"', idx).group(1)
    charges = re.search(r"/\*CHARGES_START\*/(.*?)/\*CHARGES_END\*/", idx, re.S).group(1)

    CACHE.mkdir(parents=True, exist_ok=True)
    fonts = [{"id": "sealstile", "label": "Sealstile (seal font)", "family": "Sealstile",
              "weight": 400, "b64": seal_b64}]
    for fid, label, family, spec, weight in WEBFONTS:
        p = CACHE / f"{fid}.woff2"
        if not p.exists():
            p.write_bytes(latin_woff2(spec))
            print(f"downloaded {label} -> {p.name}")
        fonts.append({"id": fid, "label": label, "family": family, "weight": weight,
                      "b64": base64.b64encode(p.read_bytes()).decode()})

    html, n = re.subn(
        r"/\*HDRFONTS_START\*/.*?/\*HDRFONTS_END\*/",
        "/*HDRFONTS_START*/const FONTS = " + json.dumps(fonts) + ";/*HDRFONTS_END*/",
        html, flags=re.S)
    assert n == 1, "HDRFONTS markers not found"

    html, n = re.subn(
        r"/\*CHARGES_START\*/.*?/\*CHARGES_END\*/",
        "/*CHARGES_START*/" + charges + "/*CHARGES_END*/",
        html, flags=re.S)
    assert n == 1, "CHARGES markers not found"

    HEADER.write_text(html)
    print(f"embedded {len(fonts)} fonts + charges; wrote {HEADER}")


if __name__ == "__main__":
    main()
