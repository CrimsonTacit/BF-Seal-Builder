# Bravo Fleet Graphics Tools

In-browser tools for making Bravo Fleet graphics without firing up Photoshop
(or asking someone who has it). Everything runs locally in your browser —
nothing is uploaded anywhere, and exports are fully self-contained files.

The hosted copy lives behind a Bravo Fleet login — sign in on
bravofleet.com and the tools are there. Running it yourself takes PHP; see
**For developers** below. Nothing opens straight off disk any more: every
tool loads its fonts and artwork by URL, and the pages themselves are PHP.

## The tools

### Seal Builder (`/seal`)

Round seals for vessels, stations, and organizations: curved ring text with
separator glyphs (Starfleet delta, BF bolt, and more), task-force emblems or
your own art in the center, procedural starfields, and per-component colors
with official Bravo Fleet palettes built in. Exports PNG (up to 4096 px) and
SVG.

### Header Builder (`/header`)

The metallic wordmark headers used on BFMS command pages — a small gold
"BRAVO FLEET" line, a divider rule, and a big brushed-metal command name
stretched to matching width. Seven metal materials plus custom colors, four
finishes (polished / brushed / satin / flat), five fonts, and optional bolt,
delta, or task-force emblems. Exports PNG **with a transparent background**
(750–3000 px wide) ready to overlay on a cover image, plus SVG.

Tip: use the stage's "Image…" background option to preview your header on
top of your actual BFMS cover photo before exporting.

### Banner Builder (`/banner`)

The ship and station banners: the gold delta and framed panel, with your
ship art inside the frame and the name, registry, and class set in the
fleet's lettering. The design itself is fixed — the one choice is which
delta flies on it, Bravo Fleet's or any task force's. Exports a transparent
PNG at 1400 px or double size.

### Plaque Builder (`/plaque`)

Dedication plaques, built from the original 2399 plaque artwork rather than
a redraw of it: twelve backing colours, seven trim finishes, and any custom
colour on top of those. Edit the ship name, registry, the two upper corner
blocks, the dedication quote, and as many roster columns as you like — then
choose whether the lettering, trim, and delta are **raised** off the plate
or **engraved** into it. Exports PNG up to 4700 px.

Tip: in a roster column, start a line with `##` to run a second heading
partway down it (that's how "Admiralty Board" sits under "Chiefs of Staff").

## Fonts & licenses

All bundled fonts are libre (SIL Open Font License 1.1), so the tools can
be shared freely:

- **Sealstile** — our patched build of
  [Librestile](https://github.com/ocelothe/Librestile) Ext Bold by
  ocelothe2k1 (license: `site/fonts/OFL-Librestile.txt`), with a bullet glyph
  added and renamed per the OFL's Reserved Font Name rule.
- **Tenor Sans**, **Cinzel**, **Michroma**, **Orbitron** — from Google
  Fonts, used by the Header Builder.
- **EB Garamond** — from Google Fonts, used for the plaque's ship name and
  dedication quote (standing in for the original's Adobe Garamond).

Bravo Fleet emblems and names are © [Bravo Fleet](https://bravofleet.com);
these tools are for use within the fleet. Original seal design by
**CrimsonTacit**; original Columbia header by **JustSlide**.

## For developers

No build step and no JavaScript dependencies — but the pages are PHP, so
you need **PHP 8.3 or newer** on your PATH (`brew install php` on macOS,
`apt install php-cli` on Debian/Ubuntu). Every tool also loads its fonts and
artwork from `assets/` and `fonts/` by relative URL, so a static server is
not enough and `file://` will not work at all:

```bash
python3 tools/serve_site.py 8517
```

That starts `php -S` on the `site/` webroot with `tools/dev-router.php`,
which gives you the same extensionless routes Apache serves — `/`, `/seal`,
`/header`, `/banner`, `/plaque`, `/patch`, `/mission`.

`site/` is the webroot and everything else stays out of it. On the real host
each page requires `site/auth.php`, which checks you are logged in to Bravo
Fleet; that check returns early under PHP's built-in server, so local
development needs no WordPress install. Copy `site/auth-config.php.example`
to `site/auth-config.php` (gitignored) to point a deployment at its own
`wp-load.php`.

Exports still come out fully self-contained: an SVG rasterised through an
`<img>` can't fetch anything, so each tool inlines the fonts and art the
current design uses as data URIs at export time.

If you change the font or emblem sources, refresh the registries with:

```bash
python3 tools/embed_assets.py            # seal tool: TF emblem registry (stdlib only)
python3 tools/embed_assets.py --font     # + rebuild Sealstile (needs fontTools + brotli)
python3 tools/embed_header_assets.py     # header tool: fonts + emblems (run after the above)
python3 tools/fetch_shared_fonts.py      # loose webfonts for the other tools
```

These are idempotent — a re-run leaves the HTML byte-identical. Webfonts are
cached in `site/fonts/webfonts/` after the first run, so rebuilds work offline.

Shared code lives in `site/shared/`: `chrome.css` (the dark-console UI every
tool wears), `state.js` (seeded PRNG, slugs, debounced localStorage), `text.js`
(SVG text measurement), `stage.js` (the canvas background) and `export.js`
(asset inlining + the export pipeline).

Before deploying, start the server and open
`http://127.0.0.1:8517/tests/smoke.html`. It loads every page and checks that
each one's assets resolve on the server and that its PNG export renders and
comes out self-contained — no build step, no dependencies. `tests/` sits
outside the webroot, so only the dev server serves it; CI runs the same suite
headlessly with `node tools/run_smoke.mjs`.

The plaque artwork is extracted from the source PSD by
`tools/extract_plaque_assets.py`, which needs `pip install
'psd-tools[composite]'`. It isn't part of the normal build — run it only if
the PSD changes, and commit what it writes to `site/assets/plaque/`. See
`CLAUDE.md` for architecture notes.
