# AGENTS.md

Guidance for AI coding agents (and humans) working in this repository. Read this before making changes.

## Project Overview

- **Project:** MJHS Elderplan 2026 — a WordPress site.
- **Theme:** `sd_base` (Sandstorm Design base theme) — the only custom theme in the repo. All theme work happens in [web/wp-content/themes/sd_base](web/wp-content/themes/sd_base).
- **Local dev:** [DDEV](https://ddev.com/). Config in [.ddev/config.yaml](.ddev/config.yaml).
- **Production host:** Liquid Web. Keep build artifacts and deployment assumptions compatible with a standard LAMP-style host (no edge/serverless features).
- **Dependency management:** Composer at the project root manages WordPress core (via composer/installers paths), Timber, and ACF Pro. Plugins and core PHP files are gitignored — see [.gitignore](.gitignore).

## Stack

- **WordPress** with a custom directory layout: web root is [web/](web), Composer installs core/plugins/themes via [composer.json](composer.json).
- **Timber 2.x + Twig** for all templating. PHP template files in the theme are thin shims that hand off to Twig via `Timber::render()` / `Timber::compile()`.
- **ACF Pro** (installed via Composer from `connect.advancedcustomfields.com`) for fields and ACF Blocks. Auth token lives in [auth.json](auth.json) — do not commit changes that leak it.
- **Sass (Dart Sass)** compiled by a small custom Node script: [web/wp-content/themes/sd_base/tools/build-scss.mjs](web/wp-content/themes/sd_base/tools/build-scss.mjs). No webpack/vite.
- **Node 20.19.3** (pinned via Volta in [package.json](web/wp-content/themes/sd_base/package.json) and [.nvmrc](web/wp-content/themes/sd_base/.nvmrc)).

## Local Setup

```bash
ddev start
ddev composer install
ddev import-db --file=database/wordpress-db.sql.gz
ddev launch
```

CSS build (run inside DDEV):

```bash
ddev fe_compile
```

Or directly in the theme:

```bash
cd web/wp-content/themes/sd_base
npm install
npm run build:css     # one-shot
npm run watch:css     # watch mode
```

## Theme Architecture: Componentized Design

The theme follows an atomic / componentized structure under [web/wp-content/themes/sd_base/src](web/wp-content/themes/sd_base/src):

- `0-styles/` — global SCSS, design tokens, and shared utilities (`@util` import alias).
- `1-elements/` — atomic UI primitives (`button`, `image`, `logo`, `tag`, `inputs`, `breadcrumbs`, `social-share`).
- `2-components/` — composed UI pieces (`card`, `teaser-block`, `video-modal`).
- `3-sections/` — larger page sections (`hero`, `header`, `footer`, `story-slider-block`, etc.).
- `4-pages/` — page-level templates (`base.twig`, `front-page.twig`, `single.twig`, plus folders for specific page types).

### One folder per component

Each component lives in its own folder and co-locates everything it needs:

```
src/2-components/teaser-block/
  teaser-block.twig    # Twig template
  teaser-block.scss    # styles (optional)
  teaser-block.json    # sample data for stories/preview (optional)
```

Twig is registered for all four tier directories — see `Timber::$dirname` in [functions.php](web/wp-content/themes/sd_base/functions.php).

### Styling rules

- Write SCSS, not plain CSS, in component folders.
- One SCSS entry per file produces one CSS file in `dist/css/<name>.css`. Files starting with `_` and anything in `0-styles/utils` or `0-styles/stories` are partials and are skipped by the build (see [tools/build-scss.mjs](web/wp-content/themes/sd_base/tools/build-scss.mjs)).
- Use `@use '@util' as *;` to pull in shared mixins/variables.
- Class naming: BEM-style, scoped to the component (e.g. `.teaser-block`, `.teaser-block__content`).
- Global enqueues (`global.css`, `header.css`, `footer.css`, `page-content.css`) are wired in `sd_enqueue_global_assets()`. Per-template CSS/JS is auto-loaded by `load_assets($template_name)` if a matching file exists in `dist/`.

### Building Blocks (ACF Blocks)

All blocks are registered in PHP via a **data-driven** pattern in [functions.php](web/wp-content/themes/sd_base/functions.php) — see `sd_register_acf_blocks()` and the helpers `sd_acf_field()` and `sd_render_block_generic()`.

To add a new block:

1. Create the component folder and Twig template, e.g. `src/2-components/my-block/my-block.twig` (and a sibling `.scss` if it needs styles).
2. Add an entry to the `$blocks` array in `sd_register_acf_blocks()` with `name`, `title`, `description`, `icon`, `keywords`, ACF `fields` (built with `sd_acf_field()`), and a `template` path pointing at the Twig file.
3. Run `ddev fe_compile` so the new SCSS is picked up.

Notes:
- Do **not** hand-roll separate `acf_register_block_type()` calls or duplicate render callbacks — reuse `sd_render_block_generic`, which auto-locates the Twig template by block name across the four tier directories.
- Field keys must be unique and prefixed (e.g. `field_<block>_<field>`).
- Prefer ACF Blocks over classic shortcodes or hand-built Gutenberg blocks for editorial content.

### Twig conventions

- Pages extend `4-pages/base.twig` and override blocks.
- Components are rendered with `{% include 'component-name/component-name.twig' with { ... } %}` or via Timber inside a block render callback.
- Common context values (`current_url`, `document_title`, `title`, `post`) are injected via the `timber/context` filter in [functions.php](web/wp-content/themes/sd_base/functions.php).

## Conventions for Agents

- **Edit the theme, not WP core or plugins.** Everything outside `web/wp-content/themes/sd_base/` (and root config files) is either gitignored or vendor-managed.
- **Never commit:** `node_modules/`, `vendor/`, `dist/`, uploads, `.env*`, or anything matched in [.gitignore](.gitignore). The `dist/` folder is build output.
- **NEVER run build or watch commands** (`npm run build:css`, `npm run watch:css`, `ddev fe_compile`, etc.) — Richard runs these himself. Just inform him when SCSS changes need to be compiled.
- **PHP style:** 2-space indentation, snake_case functions prefixed `sd_`, early returns, and keep `functions.php` focused on bootstrap + block registration. Push presentation into Twig.
- **No business logic in templates** — fetch/transform in PHP (or a Twig filter), render in Twig.
- **Lint configs** are present: [.eslintrc.yml](web/wp-content/themes/sd_base/.eslintrc.yml), [.stylelintrc.yml](web/wp-content/themes/sd_base/.stylelintrc.yml), [.editorconfig](web/wp-content/themes/sd_base/.editorconfig). Match existing formatting.
- **Database:** seed dump lives at [database/wordpress-db.sql.gz](database/wordpress-db.sql.gz). Other `*.sql*` files are gitignored.

## Deployment Target

Production deploys to **Liquid Web**. Keep in mind:

- Standard PHP/MySQL hosting — no Node runtime in production. All JS/CSS must be pre-built and committed in `dist/` (or built in CI before deploy).
- File paths assume the `web/` document root layout from [composer.json](composer.json) `installer-paths`.
- Don't introduce dependencies that require shell access or background workers without confirming with the team.
