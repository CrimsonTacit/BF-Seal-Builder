# Bravo Fleet Graphics Tools

Single-file HTML apps for the user's Bravo Fleet continuity (Star Trek fan fleet), replacing Photoshop workflows. No build step, no dependencies — serve the folder (`.claude/launch.json` runs `python3 -m http.server 8517`). Three pages:

- [index.html](index.html) — static landing page linking to the two tools (plain CSS, no embedded assets; card "marks" mimic each tool's look).
- [seal-tool.html](seal-tool.html) — **Seal Builder**: round vessel/station/org seals.
- [header-tool.html](header-tool.html) — **Header Builder**: metallic wordmark overlays for BFMS command pages.

Reference seals the design replicates live in `examples/` (`*Seal.png`, `USS*.png`), plus `Examples/Headers/ColumbiaTitle.png` for the header look. Both tools carry a `© Bravo Fleet` credit footer (seal design by CrimsonTacit, original Columbia header by JustSlide) and cross-link to each other and the landing page.

## Seal Builder architecture (all inside seal-tool.html)

- **State**: single `S` object (see `DEFAULTS`); persisted to localStorage key `sealbuilder-v5` (uploaded images excluded — see `IMG`/`CUSTOM_CHARGE` below). Bump the key when changing state shape (boot falls back through older keys; additive fields merge cleanly over `DEFAULTS`, and the v4→v5 step also rescales text metrics for the font swap).
- **Colors**: clicking a color well opens the `#swatchpop` popover (last-applied preset's colors via `S.lastPreset`, then the deduped palette across all `PRESETS`); "Custom color…" falls through to the native picker via `showPicker()` guarded by a `nativeBypass` flag. The popover stays open while swatches are tried; outside click / Esc closes it.
- **Width locks**: `LOCK_PAIRS` ties `edgeW`↔`gapW` (`lockRings`) and `ring1W`↔`ring2W` (`lockAccents`), both on by default; dragging one slider drives its partner, clamped to the partner's slider range.
- **Rendering**: `buildSVG({embedFont})` returns the full SVG string (viewBox 1000×1000, center 500,500, outer radius 496). Live preview injects it without the font; exports embed the font via `<defs><style>@font-face…`.
- **Ring model, outside → in**: thick outer ring (`edgeW`, fill `c_edge`) → band (`bandW`, `c_band`) containing the curved text plus two thin *floating* accent rings (`ring1W`/`ring2W`, stroked circles inset by `inset` so they never touch the thick rings or text) → thick inner ring (`gapW`, `c_gap`) → center disc (`c_center`). This matches the user's original design: accent "cheat" rings must not touch anything.
- **Text**: `<textPath>` on half-circle arcs; top arc sweeps clockwise (upright over the top), bottom arc counterclockwise (upright under the bottom). Baseline radii are offset ±0.36×fontSize from band middle so both visually center in the band.
- **Separators**: `SEPARATORS` map (Starfleet delta path, BF bolt polygon from `bf-bolt-white.svg`, four-point sparkle, plain circle — all ~42–54 units tall, centered on origin), plus TF emblems via `sepStyle` values `tf:<CHARGES key>`, rendered as `<image>` sized to the delta's ~54-unit height and tinted to `c_delta` by the `#sepTintF` feFlood filter (dropdown options built in JS next to the charge dropdown). Groups of `sepCount` glyphs per side, `sepSpacing`° apart along the band's track, mirrored about the vertical axis; `sepShift` slides both groups along the track (positive = toward the top text, for long bottom text — see `examples/Organization Examples/`). A glyph at track angle φ gets rotation φ + `sepRot` (right) / (φ−180) − rot (left) so groups lean with the curve; in that local frame 0° = up, ±90° = radially outward on both sides. `sepMirror` links the angles (left = inverse of right); unlinked, `sepRotL` drives the left group.
- **Center charge**: task-force emblems (embedded PNGs in `CHARGES`), vector BF bolt, or a user-uploaded "Custom image…" (`S.charge === "custom"`, image data in the top-level `CUSTOM_CHARGE` var — kept out of `S`/localStorage like the background `IMG`, so it doesn't survive reload), clipped to the center circle; optional recolor via `feFlood`+`feComposite in SourceAlpha` filter (works in canvas export, and applies to a custom image the same as a built-in emblem — it flattens the shape's alpha to one color, so it only suits silhouette-style art, not full-color photos/logos).
- **Starfield**: seeded (`mulberry32`) so a given `starSeed` is stable. Big four-point sparkles (`starSparkles` toggle) are placed first with mutual separation, then small stars rejection-sample away from every sparkle's arm radius; attempt caps keep the loops bounded, so extreme densities may place fewer stars than `starDensity`.
- **Exports**: SVG = serialized string download. PNG = SVG blob → `<img>` → canvas → toBlob. Both are fully portable (font + images embedded as data URIs).
- **Collapsible panel sections**: each `#panel` section (except the trailing Reset-to-defaults one) is a native `<details class="acc" open><summary>…</summary><div class="acc-body">…</div></details>`, not JS-driven — the browser handles open/close state. `summary::before`/`::after` draw the rotating chevron and trailing rule that used to live on `section h2`.
- **UI accent colors**: `--bf-blue` (`#2864a8`, matches the Bravo Blue preset) and `--bolt-gold` (`#d3a92c`, matches Bolt Gold) are the app-chrome accent variables — chosen to match Bravo Fleet's actual brand colors rather than arbitrary teal/gold.

## Embedded assets

`FONT_B64` ("Sealstile" WOFF2) and `CHARGES` (TF emblem PNGs from `TFEmblems/`) are base64 constants. Regenerate with:

```bash
python3 tools/embed_assets.py          # emblems (stdlib only)
python3 tools/embed_assets.py --font   # + font (needs a venv with fonttools & brotli)
```

**Font**: "Sealstile" is `fonts/LibrestileExtBold.ttf` (Librestile by ocelothe2k1, SIL OFL 1.1 — `fonts/OFL-Librestile.txt`), patched by `embed_assets.py --font`: adds a bullet glyph (Librestile has no U+2022, and "•" is the seal separator convention; circle sized to match the original Microstyle bullet — 0.84×cap diameter, center 0.60×cap up), aliases en dash→hyphen and U+2019→apostrophe, and renames the family ("Librestile" is an OFL Reserved Font Name, so the modified font can't keep it). It replaced the original Microstyle Bold Extended (1991 Agfa, copyrighted — undistributable) in the v5 storage bump; Sealstile runs ~13% wider at equal size, so the boot migration from v4-and-older keys scales `fontSize` ×46/52 and `letterSpacing` ×5/8, and `DEFAULTS` moved from 52/8 to 46/5. Old designs re-exported from a v5 build may still need a nudge if their text was near-overflow.

## Bravo Fleet official colors (bravofleet.com/graphics)

Bravo Blue `#2864A8` · Bolt Gold `#D3A92C` · TF17 Gray `#434E5F` · TF21 Purple `#651060` · TF47 Orange `#C64F1C` · TF72 Navy `#20347F` · TF86 Red `#7C0309` · TF93 Green `#1A4A3C`. These exist as presets in `PRESETS` (band = TF color, darker shade for edge/center computed by eye). BF graphics are copyrighted; this tool is for the user's in-fleet use.

`PRESETS` also has an unofficial "Department Inspired" group (Command Red, Sciences Blue, Operations Gold, Medical White) for inspiration, and an "Other" group of hand-picked fictional themes (Sea Greens, Pegasus, Federation, Healer) — same darker-shade-by-eye convention, not tied to any real fleet's graphics. Every preset sets `c_edge`/`c_gap` to the same color and `c_ring1`/`c_ring2` to the same color (the click handler also forces `edgeW`/`gapW` to 20), and `c_delta` (separators) must differ from `c_band` — check new presets against this before adding them. Each preset's `group` field controls which labeled section it renders under in the Presets panel (array order = section order = button order within a section).

## Header Builder (header-tool.html)

Parallel single-file app for BFMS page-header wordmarks (reference: `Examples/Headers/ColumbiaTitle.png`, in use at bravofleet.com command pages). Same dark-console chrome, state object `S` (localStorage `headerbuilder-v1`), and export pipeline (SVG string → blob → canvas for PNG; exports embed only the fonts actually used). Key differences from the seal app:

- **Model**: logical canvas 1500 wide, height computed by `layout()` from enabled rows: optional top line → optional divider rule (lozenge `rx`) → main line → optional sub line. Lines with "Fit to block width" use `textLength`/`lengthAdjust="spacing"` to exactly fill `blockW` (the signature justified-header look); if a line is naturally *wider* than the block, `layout()` shrinks its font size via canvas `measureText` instead of letting spacing go negative.
- **Materials/finishes**: `MATERIALS` = named multi-stop vertical ramps with a hard mid "horizon" break (gold, silver, platinum, copper, bronze, steel, gunmetal) + `custom` (ramp generated from a picked color via `rampFor`). `FINISHES`: polished / brushed / satin / flat map to `finishParams` (specular constant+exponent, texture on/off). Per element (top/main/sub/rule/emblem): gradient fill + dark edge stroke (`paint-order:stroke`) + filter = optional `feTurbulence` brushed streaks (overlay blend, re-masked by SourceAlpha) → `feSpecularLighting` bevel highlight (blur of SourceAlpha, composited `in` then arithmetic-added at k3=0.85) → `feDropShadow`. Global Finish sliders (bevel/shine/texture/shadow) scale these. Filters are what make it "metal" — tune `finishParams` and the ramps together, and keep specular restrained or glyph interiors fog white.
- **Emblems**: BF bolt / delta / sparkle vectors (shared with seal separators) or TF emblems from `CHARGES`. TF art is tinted metallic via an alpha `<mask>` over a gradient rect (mask on inner element, filter on outer `<g>` so the bevel sees the emblem's alpha, not the rect's), or kept as-is with material "original" (`matFor` falls back to silver for that key since the gradient is still emitted). Placements: on the divider (rule splits, layout reserves headroom), centered above, or flanking the top/main line (hugs measured text width, clamped to canvas).
- **Embedded fonts**: `FONTS` array = Sealstile (copied from seal-tool.html) + four OFL Google Fonts (Tenor Sans — closest to the Optima-style reference, Cinzel 600, Michroma, Orbitron 600), latin subsets only. Regenerate with `python3 tools/embed_header_assets.py` (stdlib; downloads are cached in `fonts/webfonts/` so it's offline after first run). Run it after `embed_assets.py` if the seal font or emblems changed. `CAP` holds per-font cap-height ratios used by row layout.
- **Stage**: checker / dark / light / user-uploaded image backgrounds (preview only, never exported).

## Testing notes

- Use the launch.json server (`http://localhost:8517`) — the browser pane renders `file://` pages as `data:` snapshots where data-URI font loads fail with NetworkError, which looks like a font bug but isn't.
- Verify PNG export by calling `renderToCanvas(1000)` in the console and sampling pixels rather than downloading.
- User's UI font conventions: app chrome uses the embedded seal font (Sealstile) for headings; keep the dark console aesthetic.
