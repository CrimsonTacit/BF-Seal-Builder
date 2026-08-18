#!/usr/bin/env python3
"""Stamp every asset reference with a content hash, so a changed file is a new URL.

    python3 tools/stamp_assets.py           # rewrite the HTML
    python3 tools/stamp_assets.py --check   # exit 1 if any stamp is stale

Every tool loads its fonts, artwork and shared code by relative URL. Change a
file in place -- same name, new bytes -- and a browser holding the old copy
keeps using it. GitHub Pages caps that at ten minutes (it sends
`cache-control: max-age=600` with an ETag, so the next revalidation picks the
change up), but a plain `python3 -m http.server` sends no cache headers at all
and the browser then caches heuristically for far longer. That is the case that
actually bites: with fonts/sealstile.woff2 deleted and the server returning 404,
the tools kept exporting perfectly from cache.

`foo.png?v=a1b2c3d4` makes the URL itself change with the bytes, so a stale copy
can never be reused. The version is the first 8 hex of the file's SHA-256, so it
is stable, reproducible from the file alone, and only moves when the file does.

Idempotent: re-running with nothing changed leaves the HTML byte-identical.
Run it after tools/embed_assets.py and tools/embed_header_assets.py, which write
the registries this then stamps. CI enforces that with --check.
"""
import hashlib
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
PAGES = sorted(ROOT.glob("*.html")) + sorted((ROOT / "tests").glob("*.html"))

# any quoted relative path into these, with or without an existing ?v= stamp
REF = re.compile(r'(["\'])((?:assets|fonts|shared)/[^"\'?#]+\.[A-Za-z0-9]+)(\?v=[0-9a-f]+)?\1')


def version(rel: str):
    p = ROOT / rel
    if not p.is_file():
        return None
    return hashlib.sha256(p.read_bytes()).hexdigest()[:8]


def stamp(text: str, missing: list):
    def sub(m):
        quote, rel, _old = m.group(1), m.group(2), m.group(3)
        v = version(rel)
        if v is None:
            missing.append(rel)
            return m.group(0)
        return f"{quote}{rel}?v={v}{quote}"
    return REF.sub(sub, text)


def main():
    check = "--check" in sys.argv
    stale, missing, touched = [], [], 0
    for page in PAGES:
        old = page.read_text()
        new = stamp(old, missing)
        if new != old:
            stale.append(page.relative_to(ROOT).as_posix())
            if not check:
                page.write_text(new)
                touched += 1

    if missing:
        for rel in sorted(set(missing)):
            print(f"referenced but not on disk: {rel}", file=sys.stderr)
        return 1

    if check:
        if stale:
            print("stale asset stamps in: " + ", ".join(stale), file=sys.stderr)
            print("run: python3 tools/stamp_assets.py", file=sys.stderr)
            return 1
        print(f"all asset stamps current across {len(PAGES)} pages")
        return 0

    print(f"stamped {touched} of {len(PAGES)} pages" if touched else
          f"all {len(PAGES)} pages already current")
    return 0


if __name__ == "__main__":
    sys.exit(main())
