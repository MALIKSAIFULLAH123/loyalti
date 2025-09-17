const chalk = require('chalk');
const path = require('path');
const fs = require('fs');
const glob = require('glob');

if (!process.env.WORKBOX_ROOT) {
  require('../config/env');
  if (!process.env.WORKBOX_ROOT) {
    console.log(chalk('process.env.WORKBOX_ROOT does not exists!'));
    process.exit(0);
  }
}

function getRootDir() {
  return process.env.WORKBOX_ROOT.replace(/(\/+)$/, '');
}

function getRootJSONFile(filename) {
  const jsonPath = path.resolve(getRootDir(), filename);
  if (!fs.existsSync(jsonPath)) {
    throw new Error(`${jsonPath} does not exists!`);
  }
  return require(jsonPath);
}

function getPackageDirs(rootDir) {
  const json = getRootJSONFile('package.json');
  if (!json.workspaces || !json.workspaces.packages) {
    return [];
  }
  return json.workspaces.packages;
}

function getAllDevPackages() {
  const rootDir = getRootDir();
  return getPackageDirs(rootDir)
    .map(str => str.replace(/(\/+)$/, ''))
    .reduce((acc, dir) => {
      glob
        .sync(`${dir}/package.json`, {
          realpath: false,
          cwd: rootDir,
          follow: true
        })
        .forEach(file => acc.push(path.dirname(file)));
      return acc;
    }, [])
    .filter(Boolean)
    .map(x => path.resolve(rootDir, x));
}

function getAllDevPackageJsonFiles() {
  return getAllDevPackages().map(dir => `${dir}/package.json`);
}

module.exports = {
  getRootDir,
  getPackageDirs,
  getAllDevPackages,
  getAllDevPackageJsonFiles
};
