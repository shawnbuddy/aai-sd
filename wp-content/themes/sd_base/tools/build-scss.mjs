import path from 'node:path';
import { mkdir, writeFile } from 'node:fs/promises';
import { pathToFileURL } from 'node:url';
import { globSync } from 'glob';
import { compileAsync } from 'sass';

const THEME_ROOT = process.cwd();
const SRC_GLOB = path.join(THEME_ROOT, 'src/**/*.scss');
const DIST_CSS = path.join(THEME_ROOT, 'dist/css');
const UTIL_SCSS = path.join(THEME_ROOT, 'src/0-styles/utils/util.scss');
const watchMode = process.argv.includes('--watch');
const browsersyncMode = process.argv.includes('--browsersync');

let bs = null;

if (browsersyncMode) {
  const emitWarning = process.emitWarning.bind(process);

  process.emitWarning = (warning, type, code, ...args) => {
    if (type === 'DeprecationWarning' && code === 'DEP0060') {
      return;
    }

    emitWarning(warning, type, code, ...args);
  };
}

function getScssEntries() {
  return globSync(SRC_GLOB, {
    ignore: [
      '**/_*.scss',
      '**/0-styles/utils/*.scss',
      '**/0-styles/stories/**/*.scss',
    ],
    nodir: true,
  });
}

async function compileEntry(filePath) {
  const name = path.basename(filePath, '.scss');
  const outputPath = path.join(DIST_CSS, `${name}.css`);

  const result = await compileAsync(filePath, {
    style: 'expanded',
    loadPaths: [path.join(THEME_ROOT, 'src/0-styles/utils')],
    sourceMap: true,
    importers: [
      {
        findFileUrl(url) {
          if (url === '@util') {
            return pathToFileURL(UTIL_SCSS);
          }

          return null;
        },
      },
    ],
  });

  await mkdir(path.dirname(outputPath), { recursive: true });

  // Write source map and add sourceMappingURL to CSS
  if (result.sourceMap) {
    const cssWithSourceMap = result.css + `\n/*# sourceMappingURL=${name}.css.map */`;
    await writeFile(outputPath, cssWithSourceMap);
    await writeFile(`${outputPath}.map`, JSON.stringify(result.sourceMap));
  } else {
    await writeFile(outputPath, result.css);
  }

  console.log(`Compiled ${path.relative(THEME_ROOT, filePath)} -> dist/css/${name}.css`);
}

async function buildAll() {
  const entries = getScssEntries();
  if (!entries.length) {
    console.log('No SCSS entries found.');
    return;
  }

  for (const entry of entries) {
    await compileEntry(entry);
  }

  // Stream CSS changes to Browsersync (inject without reload)
  if (bs) {
    bs.reload('*.css');
  }
}

await buildAll();

if (watchMode) {
  const { watch } = await import('node:fs');

  // Initialize Browsersync if flag is present
  if (browsersyncMode) {
    const browserSync = (await import('browser-sync')).default;
    bs = browserSync.create();

    bs.init({
      proxy: 'https://mjhs-elderplan-2026.ddev.site',
      port: 3003,
      ui: {
        port: 3004,
      },
      files: [
        path.join(THEME_ROOT, '**/*.php'),
        path.join(THEME_ROOT, 'src/**/*.twig'),
        path.join(THEME_ROOT, 'dist/js/**/*.js'),
      ],
      open: true,
      notify: false,
      ghostMode: false,
      reloadDelay: 100,
      https: true,
    });

    console.log('Browsersync is running at https://localhost:3003.');
  }

  console.log('Watching SCSS files for changes...');

  watch(path.join(THEME_ROOT, 'src'), { recursive: true }, async (_eventType, fileName) => {
    if (!fileName || !fileName.endsWith('.scss') || path.basename(fileName).startsWith('_')) {
      return;
    }

    try {
      await buildAll();
    } catch (error) {
      console.error(error.message);
    }
  });
}
