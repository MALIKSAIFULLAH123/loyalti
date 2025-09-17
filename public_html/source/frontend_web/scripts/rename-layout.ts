import fs from 'fs';
import chalk from 'chalk';
import glob from 'glob';
import os from 'os';
import path from 'path';
import { set } from 'lodash';

function getJson(filename: string): object {
  return JSON.parse(fs.readFileSync(filename, { encoding: 'utf-8' }));
}

export function writeJson(filename: string, data: object) {
  console.log(chalk.cyan('Updated') + chalk.green(filename));
  fs.writeFileSync(filename, JSON.stringify(data, null, '  ') + os.EOL);
}

function updateLayoutFile(filename: string) {
  console.log(`processing ${filename}`);

  const data = getJson(filename);

  const dest = filename.split('/src/').shift() + '/src/assets/pages';

  if (!fs.existsSync(dest)) {
    fs.mkdirSync(dest, { recursive: true });
  }

  // strip dirty props
  Object.keys(data).forEach(pageName => {
    let bundle = 'web';

    if (pageName.includes('admincp')) {
      bundle = 'admincp';
    } else if (pageName.includes('install.')) {
      bundle = 'installation';
    }

    set(data[pageName], 'info.name', pageName);
    set(data[pageName], 'info.bundle', bundle);

    writeJson(path.join(dest, pageName + '.json'), data[pageName]);

    // should rename to target.
  });

  // fs.unlinkSync(filename);
  // writeJson(filename, data);
}

export function scanAllLayoutFiles() {
  glob
    .sync('packages/**/*/assets/pages/*.json')
    .forEach((filename: string) => updateLayoutFile(filename));
}

scanAllLayoutFiles();
