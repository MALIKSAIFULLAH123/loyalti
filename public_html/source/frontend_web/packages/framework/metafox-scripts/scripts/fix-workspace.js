const { Workbox } = require('../workbox');
const path = require('path');
const fs = require('fs');
const chalk = require('chalk');
const fixWorkboxTspath = require('./fix-workbox-tspath');
const fixLocalLink = require('./fix-local-link');

const stripRootPath = str => str.substring(process.cwd().length + 1);

function updateLernaJson(wsPath, dirs) {
  const filename = path.resolve(wsPath, 'lerna.json');

  const json = JSON.parse(fs.readFileSync(filename, { encoding: 'utf-8' }));
  json.packages = dirs;

  fs.writeFileSync(filename, `${JSON.stringify(json, null, '  ')}\n`, {
    encoding: 'utf-8'
  });
  console.log(`Updated ${chalk.cyan(filename)}`);
}

function updatePackageJson(wsPath, dirs) {
  const filename = path.resolve(wsPath, 'package.json');

  const json = JSON.parse(fs.readFileSync(filename, { encoding: 'utf-8' }));
  json.workspaces.packages = dirs;

  fs.writeFileSync(filename, `${JSON.stringify(json, null, '  ')}\n`, {
    encoding: 'utf-8'
  });

  console.log(chalk.cyan(filename));
}

/**
 * Fix project workspace paths
 */
function ensureCorrectPaths() {
  const wsPath = process.env.WORKBOX_ROOT;
  const tsPathFile = path.resolve(wsPath, 'tsconfig.json');
  const tsPathJson = require(tsPathFile);
  tsPathJson.compilerOptions.paths = {};
  const packageDir = path.resolve(wsPath, 'packages');
  const dirs = [];

  fs.readdirSync(packageDir).forEach(item => {
    const stat = fs.lstatSync(path.resolve(packageDir, item));

    if (stat.isDirectory() || stat.isSymbolicLink()) {
      dirs.push(`packages/${item}/*`);
    }
  });

  console.log(`Updated ${chalk.cyan(JSON.stringify(dirs, null, '  '))}`);

  dirs.push('app');

  updateLernaJson(wsPath, dirs);
  updatePackageJson(wsPath, dirs);
}

function fixWorkspace(argv) {
  ensureCorrectPaths();
  fixWorkboxTspath();
  fixLocalLink();
}

module.exports = fixWorkspace;
