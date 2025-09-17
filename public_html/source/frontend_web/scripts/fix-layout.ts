import fs from 'fs';
import { get, has, isEmpty, isEqual, unset } from 'lodash';
import chalk from 'chalk';
import glob from 'glob';
import os from 'os';

function stripIfEmpty(block: object, strips: string[]) {
  strips.forEach(stripName => {
    if (isEmpty(get(block, stripName))) {
      unset(block, stripName);
    }
  });
}

function stripIfHas(block: object, strips: string[]) {
  strips.forEach(stripName => {
    if (has(block, stripName)) {
      unset(block, stripName);
    }
  });
}

function randomId(prefix: string = ''): string {
  return prefix + Math.random().toString(36).slice(8);
}

function getJson(filename: string): object {
  return JSON.parse(fs.readFileSync(filename, { encoding: 'utf-8' }));
}

function writeJson(filename: string, data: object) {
  fs.writeFileSync(filename, JSON.stringify(data, null, '  ') + os.EOL);
}

function stripJson(filename: string, strips: string[]) {
  const data = getJson(filename);

  console.log(chalk.cyan(`Processing ${filename}`));

  Object.keys(data).forEach(key => {
    strips.forEach(name => {
      if (has(data[key], name)) {
        unset(data[key], name);
      }
    });
  });
  writeJson(filename, data);
}

const stripLayouts = (files: string[], strips: string[]) => {
  files.forEach(filename => stripJson(filename, strips));
};

export function cleanupLayouts() {
  stripLayouts(glob.sync('./app/src/**/*/itemLayouts.json'), [
    'gridContainerProps',
    'gridItemProps',
    'gridVariant'
  ]);
  stripLayouts(glob.sync('./app/src/**/*/gridLayouts.json'), ['itemProps']);
}

function updateLayoutFile(filename: string) {
  console.log(`processing ${filename}`);

  const data = getJson(filename);

  // strip dirty props
  Object.keys(data).forEach(pageName => {
    Object.keys(data[pageName]).forEach(pageSize => {
      const page = data[pageName][pageSize];

      if (!page.blocks) return;

      page.blocks.forEach(block => {
        if (!block.blockId) block.blockId = randomId();

        if (has(block, 'gridLayout') && !has(block, 'itemLayout')) {
          block.itemLayout = block.gridLayout;
        }

        Object.keys(block).forEach(key => {
          stripIfHas(block, [
            'blockProps',
            'itemProps',
            'gridItemProps',
            'key',
            'gridContainerProps',
            'blockView',
            'props',
            'templateName',
            'pageSize',
            'pageName'
          ]);

          stripIfEmpty(block, ['title']);
        });
      });
    });
  });

  writeJson(filename, data);
}

export function scanAllLayoutFiles() {
  glob
    .sync('packages/**/*/*.layouts.json')
    .forEach((filename: string) => updateLayoutFile(filename));

  glob
    .sync('packages/**/*/layouts.json')
    .forEach((filename: string) => updateLayoutFile(filename));

  glob
    .sync('packages/**/*/layout.pages.json')
    .forEach((filename: string) => updateLayoutFile(filename));

  // updateLayoutFile('packages/metafox/core/src/pages/HomePage/layouts.json');
}

export function compareGridLayouts() {
  function checkDuplicatedGridLayout(filename: string) {
    const data = getJson(filename);

    for (const x in data) {
      for (const y in data) {
        if (x !== y && isEqual(data[x], data[y])) {
          console.warn(`${x} similar to ${y}`);
        }
      }
    }
  }

  const files = glob.sync('packages/**/*/gridLayouts.json');

  files.forEach(filename => checkDuplicatedGridLayout(filename));
}
scanAllLayoutFiles();
// compareGridLayouts();
// scanAllLayoutFiles();

// $ npx ts-node scripts/fix-layout.ts
