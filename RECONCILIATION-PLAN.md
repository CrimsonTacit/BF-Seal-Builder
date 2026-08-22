# Reconciling the BFE PHP fork with `main`

**Status:** carried out 2026-08-21, on branch `bfe-reconciliation`. **Written:** 2026-08-21.
**Audience:** the engineer (human or agent) who performs the merge — kept as the
record of why the tree looks the way it does. See §9 for what the plan missed.

The three decisions in §3 held. All six exports came out **bit-identical** to
the pre-move build, including header and mission (§7 trap 11 expected drift
there; it does not appear under headless software rasterization, and the
old and new builds were captured in one browser launch so the control was
matched). The smoke suite reports the same per-page asset counts as before the
move — 0/11/16/14/9/13/20 — so it is checking the same surface.

---

## 0. How to read this plan

This plan is written for an agentic implementer, so it is deliberately uneven
in how much it specifies:

- **Outcomes are stated precisely**, because they are the contract and they are
  verifiable. Every phase ends with a command whose output decides whether the
  phase is done.
- **Approach is specified only at decision points and known traps** — places
  where a locally reasonable choice produces a globally inconsistent result, or
  where a coupling costs an hour to rediscover. Those are called out explicitly
  in §3 and §7.
- **The mechanical middle is deliberately left open.** "Move this registry into
  that file" does not need choreography. Over-specifying it makes the plan
  brittle and wastes the implementer's actual strength.

Two things a plan for a human would usually omit, included here because an
agent needs them:

- **Per-phase verification**, so errors surface at the phase that caused them
  instead of accumulating silently.
- **Explicit non-goals** (§4). An agent with a broad mandate will otherwise
  "improve" adjacent code, and this merge is hard enough to review without it.

**Order matters.** Phase 2 restores the test harness before any tool code is
touched, so that every later phase is checkable rather than hopeful.

---

## 1. Situation

Two repositories diverged from common ancestor `b75631c` (2026-08-18 14:43) on
the same afternoon.

| | `origin/main` (CrimsonTacit) | `bfe/main` (BravoFleetEngineering) |
|---|---|---|
| Commits since split | 7 substantive | 2 |
| Hosting | GitHub Pages, static | Apache + PHP, authenticated |
| Deployed? | no | **yes — this is the beta** |
| Webroot | repo root | `site/` |
| Shared code | `shared/{chrome.css,export,metal,stage,state,text}` | `site/shared/{export,metal}.js`, **loaded by nothing** |
| Tool layout | one `.html` with inline `<style>`/`<script>` | `.php` + `css/<tool>.css` + `js/<tool>.js` |
| Assets | loaded by URL, content-stamped | **still base64-embedded** |
| Seal tool weight | 67 KB | **195 KB** |
| Header tool JS | — | **227 KB** |
| Smoke test + CI | yes | no |

`main`'s seven commits `bfe` lacks:

```
8ac92f6  Unify the canvas background across all six tools
05d10ca  Stamp asset URLs with a content hash
09fc982  Bump CI actions to v5
70cef9f  Run the smoke test in CI on every push
616ad09  Don't fail the smoke test on errors from other origins
ab79a7d  Add a smoke test, and document the URL-loading architecture
60293ee  Serve assets by URL, fix export failure modes, share the chrome
```

`bfe`'s two commits `main` lacks are the PHP conversion and a restructure.
Its genuinely new code is small:

```
site/auth.php                 67 lines
site/auth-config.php.example   8
site/.htaccess                12
tools/dev-router.php          19
tools/serve_site.py           35
+ one require line in each of 7 .php pages
```

The local branches `banner-taller-frame`, `ci-and-cache-busting` and
`deploy-readiness` are **fully merged into `main`** (0 commits ahead). There is
no unmerged work outside `main`. Ignore them.

---

## 2. Goal

One repository, on `main`, that:

1. Serves the six tools and the landing page behind the BFMS login.
2. Carries all seven of `main`'s commits — de-embedded assets, shared modules,
   content stamping, smoke test, CI.
3. Runs its smoke test in CI against a real PHP server.
4. Can still be developed locally without a WordPress install.

---

## 3. Decisions (already made — do not re-litigate)

### 3.1 Direction: port `bfe` onto `main`, not the reverse

`bfe`'s unique contribution is ~150 lines of auth plumbing. `main`'s is a 3×
payload reduction, four shared modules, a test suite and CI. Re-deriving the
latter against a rearranged tree is the expensive direction.

**Concretely:** start from `main`, add `site/`, move the pages into it, rename
them to `.php`, add the require line. Do **not** try to `git merge bfe/main` —
the trees disagree structurally and the conflict resolution would be larger
than the port.

### 3.2 Layout: adopt `main`'s (page + `shared/`), not the per-tool `css/`+`js/` split

The split was performed against **pre-refactor** files. It does not preserve
anything `main` lacks, and `main`'s `shared/chrome.css` already solves the
duplication the split was reaching for.

This is not a rejection of the idea on its merits. Splitting the inline
`<style>`/`<script>` out of each tool would let browsers cache per-tool code,
which `main` currently cannot do at all. **That is a legitimate follow-up and
should be recorded as one** — but it is orthogonal to this merge, and doing
both at once makes the diff unreviewable.

### 3.3 Webroot: adopt `bfe`'s `site/`

Keeping `tools/`, `tests/`, `examples/` and the PSDs outside the served tree is
a real improvement over `main`, where everything sits at the document root.
`site/.htaccess` already denies `auth.php` and `auth-config.php` on top of that.

**Consequence to handle in Phase 2:** `tests/smoke.html` then lives outside the
webroot and cannot be fetched by the browser. Fix it in `tools/dev-router.php`,
which already runs only under the dev server — serve `/tests/…` and the repo
root from there, never from Apache.

### 3.4 Keep `auth.php`'s `cli-server` bypass exactly as written

```php
if (PHP_SAPI === 'cli-server') { return; }
```

This is what makes local development and CI possible without WordPress. It is
safe because Apache and PHP-FPM never report that SAPI. **It is also load-bearing
for Phase 3** — CI serves the site with `php -S` and therefore skips auth.

---

## 4. Non-goals

Do not, in this work:

- Add saved designs, JSON export, server-side settings, or numeric slider entry.
- Change any tool's rendered output. Exported pixels must not move.
- Split tool CSS/JS into separate files (see §3.2 — follow-up, not now).
- Refactor `auth.php` beyond path adjustments.
- Touch `examples/` or regenerate any extracted artwork.

---

## 5. Phases

### Phase 0 — Baseline

1. `git fetch bfe && git fetch origin`
2. Confirm `main` is clean and green: serve it and run the existing suite.

**Verify:**
```bash
python3 -m http.server 8517 &
node tools/run_smoke.mjs --url http://127.0.0.1:8517
python3 tools/stamp_assets.py --check
```
All three must pass **before** anything moves. This is the control.

3. Record a pixel baseline for later comparison — export a PNG from each of the
   six tools at default state and keep the files outside the repo.

---

### Phase 1 — Graft the PHP wrapper onto `main`'s tree

**Outcome:** `main`'s current tool code, served from `site/`, behind auth.

1. Create `site/` and move into it: the seven pages, `shared/`, `assets/`,
   `fonts/`, `bf-bolt-white.svg`.
2. Rename pages: `index.html` → `index.php`, `seal-tool.html` → `seal.php`,
   and likewise `header`, `banner`, `plaque`, `patch`, `mission`.
   **Use the short names** — `.htaccess` and `dev-router.php` already route
   `/seal`, not `/seal-tool`.
3. Add `<?php require __DIR__ . '/auth.php'; ?>` as the **first line** of each
   of the seven pages, above `<!DOCTYPE html>`.
4. Copy from `bfe`: `site/auth.php`, `site/auth-config.php.example`,
   `site/.htaccess`, `tools/dev-router.php`, `tools/serve_site.py`.
5. Update the tools' cross-links and the landing page's six links to the
   extensionless routes (`/seal`, `/header`, …).
6. Update `.claude/launch.json` to run `tools/serve_site.py` instead of
   `python3 -m http.server`.

**Verify:**
```bash
python3 tools/serve_site.py 8517
curl -sf http://127.0.0.1:8517/seal | head -5     # HTML, not PHP source
```
Then open each route and confirm the tool renders and exports a PNG.

**Trap:** if `curl` returns literal `<?php`, PHP is not executing — check that
you are hitting `serve_site.py` and not a stray `http.server`.

---

### Phase 2 — Restore the test harness under PHP

**Outcome:** `tests/smoke.html` passes against the PHP site, from outside the
webroot.

1. Teach `tools/dev-router.php` to serve `/tests/…` and the repo-root paths the
   suite needs. Dev server only — it is never loaded by Apache.
2. `tests/smoke.html:45` — `TOOLS` currently lists `"seal-tool"`, `"header-tool"`
   … and the fetch at line ~107 appends `.html`. Update both to the new
   extensionless routes.
3. `tests/smoke.html:160` — the landing-page check is
   `doc.querySelectorAll('a[href$="-tool.html"]').length === 6`. That selector
   matches nothing now. Rewrite it against the new hrefs.
4. The asset-path regex at line ~118 matches `(assets|fonts|shared)/…`. Confirm
   it still matches once those live under `site/`, and that the `"../" + rel`
   fetch base is still correct. **Keep `{cache:"reload"}`** — the comment above
   it explains why, and it is the whole point of the check.

**Verify:**
```bash
python3 tools/serve_site.py 8517 &
node tools/run_smoke.mjs --url http://127.0.0.1:8517
```
Must be green, with all seven pages checked. A pass that silently checks
*nothing* is the failure mode to watch for here — confirm the reported check
count matches Phase 0's.

---

### Phase 3 — Repoint the build and CI scripts

**Outcome:** `stamp_assets.py --check` and the idempotency check pass on the new
layout.

1. `tools/embed_assets.py:21` — `INDEX = ROOT / "seal-tool.html"` → `site/seal.php`.
2. `tools/embed_header_assets.py:19-20` — `HEADER` and `INDEX` likewise.
3. `tools/stamp_assets.py:29-30` — `PAGES = ROOT.glob("*.html") + tests/*.html`
   must become `site/*.php` plus `tests/*.html`. Check `ROOT / rel` at line 37
   still resolves now that assets sit under `site/`.
4. `.github/workflows/smoke.yml`:
   - the **Serve the site** step must use `python3 tools/serve_site.py 8517`
     instead of `python3 -m http.server`, and its readiness `curl` must hit a
     route that exists (`/seal`, not `/index.html`);
   - add `php` to the runner if `ubuntu-latest` does not already provide 8.3+;
   - the **idempotency** step greps `git diff -- seal-tool.html header-tool.html`
     — update both filenames.

**Verify:**
```bash
python3 tools/embed_assets.py
python3 tools/embed_header_assets.py
python3 tools/stamp_assets.py
git diff --quiet -- site/seal.php site/header.php && echo "idempotent OK"
python3 tools/stamp_assets.py --check
```

---

### Phase 4 — Prove the output did not move

**Outcome:** evidence that this was a move, not a change.

Export a PNG from each of the six tools at default state and diff against the
Phase 0 baseline.

- **Seal, banner, plaque, patch** should be bit-identical.
- **Header and mission will not be**, and that is expected: their
  `feTurbulence`/`feSpecularLighting` filters do not rasterize deterministically.
  Per CLAUDE.md, measure each tool's **self-diff first as the control** (run it
  twice against itself), then compare. Anything beyond the control's noise floor
  is a real regression.

---

### Phase 5 — Documentation

1. `CLAUDE.md` — correct "Deployed on GitHub Pages" (true only of the old
   layout), document `site/` as the webroot, `auth.php` and its `cli-server`
   bypass, `serve_site.py` as the dev server, and the extensionless routes.
2. `README.md` — local setup now requires PHP 8.3+.
3. Record the per-tool CSS/JS split (§3.2) as a follow-up issue.
4. Retire the `bfe` fork as a separate line of development, or reduce it to a
   deploy remote. **Two divergent copies is what caused this.**

---

## 6. Suggested commit sequence

Keep these separate — a single "reconcile everything" commit is unreviewable.

```
1  Move the site into site/ and rename the pages to .php
2  Add the BFMS auth wrapper and dev server           (bfe's work, attributed)
3  Point the smoke test at the PHP routes
4  Point the build scripts and CI at the new layout
5  Update CLAUDE.md and README for the PHP deployment
```

Commit 2 should credit the original author — it is a port of their work, and
the history should say so.

---

## 7. Trap list

Every one of these is a real coupling verified in the current trees.

| # | Trap | Where |
|---|---|---|
| 1 | Smoke test hardcodes `-tool` names and appends `.html` | `tests/smoke.html:45`, ~107 |
| 2 | Landing-page check selects `a[href$="-tool.html"]` | `tests/smoke.html:160` |
| 3 | Asset scanner must keep `{cache:"reload"}` and the `?v=` suffix in its regex, or it passes while checking nothing | `tests/smoke.html:118` |
| 4 | Embed scripts write to hardcoded `seal-tool.html` / `header-tool.html` | `embed_assets.py:21`, `embed_header_assets.py:19-20` |
| 5 | Stamper globs `ROOT/*.html` | `stamp_assets.py:29-30` |
| 6 | CI serves with `python3 -m http.server`, which cannot execute PHP | `smoke.yml`, "Serve the site" |
| 7 | CI idempotency check greps the old filenames | `smoke.yml`, last step |
| 8 | `tests/` sits outside the `site/` webroot once it moves | §3.3 |
| 9 | Build scripts must run in order `embed_assets` → `embed_header_assets` → `stamp_assets`, or the diff check is unsound | CLAUDE.md |
| 10 | `bfe`'s `site/shared/{export,metal}.js` are orphans loaded by nothing — delete, do not merge | verified: each page loads one script |
| 11 | Header and mission PNG exports differ run-to-run; measure the self-diff as control | CLAUDE.md, Phase 4 |

---

## 8. Done when

- [ ] All seven routes serve behind auth on the real host.
- [ ] `node tools/run_smoke.mjs` green, with the same check count as Phase 0.
- [ ] `python3 tools/stamp_assets.py --check` clean.
- [ ] Build scripts idempotent; CI green on push.
- [ ] Seal/banner/plaque/patch exports bit-identical to baseline; header/mission
      within their measured self-diff.
- [ ] Seal tool ships ~67 KB, not ~195 KB. Header JS is not 227 KB.
- [ ] `CLAUDE.md` describes the PHP deployment.
- [ ] One line of development, not two.

---

## 9. What the plan missed

Four couplings that only surfaced during the work. Recorded here because the
next structural change will hit the same class of thing.

1. **`auth.php`'s login card pulled `/css/{themes,base,auth}.css`** — files that
   exist only in bfe's per-tool stylesheet split, which §3.2 rejects. Porting
   auth.php verbatim would have shipped an unstyled login page. It now links
   `shared/chrome.css`, which already defined every token the card used, and
   carries its own dozen rules inline.

2. **Three more scripts write into `assets/` and `fonts/`** than the three §5
   Phase 3 lists: `extract_plaque_assets.py`, `fetch_plate_textures.py` and
   `fetch_shared_fonts.py`. None runs in CI, so they would have quietly rebuilt
   a stray root-level `assets/plaque/` the next time anyone touched the PSD.
   All now resolve through a `SITE` constant.

3. **`php -S` answers an unrecognised path with the nearest `index.php`.** A
   stale or typo'd URL came back 200 with the landing page rather than 404 —
   neither Apache nor `http.server` does that, and a suite built to catch
   missing assets should not be run against a server that invents them.
   `dev-router.php` now 404s anything that is not a route, a test file, or a
   real file in the webroot. (Real missing assets did 404 correctly either way;
   this was a latent trap, not an active one.)

4. **The `og:` and `twitter:` meta tags pointed at the GitHub Pages URL.**
   Behind a login no crawler can fetch these pages, so the tags were dropped
   rather than repointed. `<meta name="description">` stays — the smoke test
   checks for it.

Also worth knowing: `site/auth-config.php` is gitignored (bfe did this too),
and `README.md` still has no Patch or Mission Poster sections. That gap predates
this merge and was left alone under §4.
