# Seal Builder

A single-file HTML app ([index.html](index.html)) for designing round seals for vessels, stations, and organizations in the user's Bravo Fleet continuity (Star Trek fan fleet). It replaces a Photoshop workflow. No build step, no dependencies — open index.html in a browser (or serve it; `.claude/launch.json` runs `python3 -m http.server 8517`).

Reference seals the design replicates live in this folder (`*Seal.png`, `USS*.png`, `Starfighter*.png`).

## Architecture (all inside index.html)

- **State**: single `S` object (see `DEFAULTS`); persisted to localStorage key `sealbuilder-v3` (uploaded image excluded). Bump the key when changing state shape (boot falls back to reading the previous key, since additive fields merge cleanly over `DEFAULTS`).
- **Rendering**: `buildSVG({embedFont})` returns the full SVG string (viewBox 1000×1000, center 500,500, outer radius 496). Live preview injects it without the font; exports embed the font via `<defs><style>@font-face…`.
- **Ring model, outside → in**: thick outer ring (`edgeW`, fill `c_edge`) → band (`bandW`, `c_band`) containing the curved text plus two thin *floating* accent rings (`ring1W`/`ring2W`, stroked circles inset by `inset` so they never touch the thick rings or text) → thick inner ring (`gapW`, `c_gap`) → center disc (`c_center`). This matches the user's original design: accent "cheat" rings must not touch anything.
- **Text**: `<textPath>` on half-circle arcs; top arc sweeps clockwise (upright over the top), bottom arc counterclockwise (upright under the bottom). Baseline radii are offset ±0.36×fontSize from band middle so both visually center in the band.
- **Separators**: `SEPARATORS` map (Starfleet delta path, BF bolt polygon from `bf-bolt-white.svg`). Groups of `sepCount` glyphs per side, `sepSpacing`° apart along the band's track, mirrored about the vertical axis; `sepShift` slides both groups along the track (positive = toward the top text, for long bottom text — see `examples/Organization Examples/`). A glyph at track angle φ gets rotation φ + `sepRot` (right) / (φ−180) − rot (left) so groups lean with the curve; in that local frame 0° = up, ±90° = radially outward on both sides. `sepMirror` links the angles (left = inverse of right); unlinked, `sepRotL` drives the left group.
- **Center charge**: task-force emblems (embedded PNGs in `CHARGES`) or vector BF bolt, clipped to the center circle; optional recolor via `feFlood`+`feComposite in SourceAlpha` filter (works in canvas export).
- **Exports**: SVG = serialized string download. PNG = SVG blob → `<img>` → canvas → toBlob. Both are fully portable (font + images embedded as data URIs).

## Embedded assets

`FONT_B64` (Microstyle WOFF2) and `CHARGES` (TF emblem PNGs from `TFEmblems/`) are base64 constants. Regenerate with:

```bash
python3 tools/embed_assets.py          # emblems (stdlib only)
python3 tools/embed_assets.py --font   # + font (needs a venv with fonttools & brotli)
```

**Font gotcha**: the seal font is `~/Library/Fonts/microsbe.ttf` ("Microstyle Bold Extended ATT", 1991 Agfa). Browsers' OTS sanitizer rejects the raw file — `usWeightClass` is 7 (legacy 1–9 scale) and the `PCLT`/`kern` tables are bad. `embed_assets.py --font` repairs it (weightClass 700, fsSelection 0x40, macStyle 0, drop PCLT/kern) and converts to WOFF2.

## Bravo Fleet official colors (bravofleet.com/graphics)

Bravo Blue `#2864A8` · Bolt Gold `#D3A92C` · TF17 Gray `#434E5F` · TF21 Purple `#651060` · TF47 Orange `#C64F1C` · TF72 Navy `#20347F` · TF86 Red `#7C0309` · TF93 Green `#1A4A3C`. These exist as presets in `PRESETS` (band = TF color, darker shade for edge/center computed by eye). BF graphics are copyrighted; this tool is for the user's in-fleet use.

## Testing notes

- Use the launch.json server (`http://localhost:8517`) — the browser pane renders `file://` pages as `data:` snapshots where data-URI font loads fail with NetworkError, which looks like a font bug but isn't.
- Verify PNG export by calling `renderToCanvas(1000)` in the console and sampling pixels rather than downloading.
- User's UI font conventions: app chrome uses the embedded Microstyle for headings; keep the dark console aesthetic.
