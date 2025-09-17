const { Workbox } = require('../workbox');
const path = require('path');
const fs = require('fs');
const chalk = require('chalk');

/**
 * update paths.
 */
module.exports = function fixPath() {
  const wsPath = process.env.WORKBOX_ROOT;
  const tsPathFile = path.resolve(wsPath, 'tsconfig.json');
  const tsPathJson = require(tsPathFile);
  tsPathJson.compilerOptions.paths = {};

  const files = Workbox.getAllDevPackages().map(dir => `${dir}/package.json`);

  files.forEach(function (file) {
    const packageJson = require(file);
    if (
      !fs.existsSync(path.resolve(file, '../src')) ||
      !fs.existsSync(path.resolve(file, '../tsconfig.json'))
    ) {
      return;
    }

    const name = packageJson.name;
    const dirname = path
      .resolve(file, '../src')
      .substr(wsPath.length)
      .replace(path.sep, '');

    tsPathJson.compilerOptions.paths[name] = [`${dirname}`];
    tsPathJson.compilerOptions.paths[`${name}/*`] = [`${dirname}/*`];
  });

  const newContent = JSON.stringify(tsPathJson, null, '  ');

  fs.writeFileSync(tsPathFile, newContent + '\n', {
    encoding: 'utf8'
  });
};
