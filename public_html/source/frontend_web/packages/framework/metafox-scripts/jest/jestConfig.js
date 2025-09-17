const fs = require('fs');
const chalk = require('chalk');
const path = require('path');
const createJestConfig = require('./createJestConfig');

const isWorkspace = fs.existsSync(path.resolve(process.cwd(), 'packages'));
const packageJson = require(path.resolve(process.cwd(), './package.json'));

if (!process.env.WORKBOX_ROOT) {
  process.env.WORKBOX_ROOT = isWorkspace
    ? process.cwd()
    : path.resolve('../../');
}

const rootDir = process.env.WORKBOX_ROOT;

module.exports = createJestConfig({
  isWorkspace,
  rootDir,
  packageName: packageJson.name,
  relativePath: path.resolve(process.cwd()).substr(rootDir.length + 1)
});
