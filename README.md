# Bravo Fleet Graphics Tools

In-browser tools for making Bravo Fleet graphics without firing up Photoshop
(or asking someone who has it). Everything runs locally in your browser —
nothing is uploaded anywhere, and exports are fully self-contained files.

**[index.html](index.html)** is the landing page. Open it in any modern
browser, or serve the folder (`python3 -m http.server`) and browse to it.

## The tools

### Seal Builder (`seal-tool.html`)

Round seals for vessels, stations, and organizations: curved ring text with
separator glyphs (Starfleet delta, BF bolt, and more), task-force emblems or
your own art in the center, procedural starfields, and per-component colors
with official Bravo Fleet palettes built in. Exports PNG (up to 4096 px) and
SVG.

### Header Builder (`header-tool.html`)

The metallic wordmark headers used on BFMS command pages — a small gold
"BRAVO FLEET" line, a divider rule, and a big brushed-metal command name
stretched to matching width. Seven metal materials plus custom colors, four
finishes (polished / brushed / satin / flat), five fonts, and optional bolt,
delta, or task-force emblems. Exports PNG **with a transparent background**
(750–3000 px wide) ready to overlay on a cover image, plus SVG.

Tip: use the stage's "Image…" background option to preview your header on
top of your actual BFMS cover photo before exporting.

## Fonts & licenses

All embedded fonts are libre (SIL Open Font License 1.1), so the tools can
be shared freely:

- **Sealstile** — our patched build of
  [Librestile](https://github.com/ocelothe/Librestile) Ext Bold by
  ocelothe2k1 (license: `fonts/OFL-Librestile.txt`), with a bullet glyph
  added and renamed per the OFL's Reserved Font Name rule.
- **Tenor Sans**, **Cinzel**, **Michroma**, **Orbitron** — from Google
  Fonts, embedded in the Header Builder.

Bravo Fleet emblems and names are © [Bravo Fleet](https://bravofleet.com);
these tools are for use within the fleet. Original seal design by
**CrimsonTacit**; original Columbia header by **JustSlide**.

## For developers

Each tool is a single HTML file with its assets embedded as base64 — no
build step, no dependencies. If you change the font or emblem source files,
re-embed with:

```bash
python3 tools/embed_assets.py            # seal tool: TF emblems (stdlib only)
python3 tools/embed_assets.py --font     # + rebuild Sealstile (needs fontTools + brotli)
python3 tools/embed_header_assets.py     # header tool: fonts + emblems (run after the above)
```

The header-tool webfonts are cached in `fonts/webfonts/` after the first
run, so rebuilds work offline. See `CLAUDE.md` for architecture notes.
