const { Workbox } = require('../workbox');
const fs = require('fs');
const rimraf = require('rimraf');
const chalk = require('chalk');
const path = require('path');

function cleanPackageDistDirectory() {
  const rootDir = Workbox.getRootDir();
  Workbox.getAllDevPackages().forEach(packageDir => {
    ['./dist', './coverage'].forEach(folder => {
      const subDir = path.resolve(packageDir, folder);
      if (!fs.existsSync(subDir)) {
        return;
      }
      console.log(
        chalk.yellow(`Cleaning ${subDir.substr(rootDir.length + 1)}`)
      );
      rimraf.sync(`${subDir}`);
    });
  });
}

try {
  cleanPackageDistDirectory();
} catch (err) {
  console.error(err);
  process.exit(1);
}
