const { Workbox } = require('../workbox');
const path = require('path');
const fs = require('fs');
const chalk = require('chalk');

module.exports = function fixLocalLink() {
  const rootDir = Workbox.getRootDir();

  Workbox.getAllDevPackageJsonFiles().forEach(function (file) {
    const packageJson = require(file);
    if (
      !fs.existsSync(path.resolve(file, '../src')) ||
      !fs.existsSync(path.resolve(file, '../dist')) ||
      !fs.existsSync(path.resolve(file, '../tsconfig.json'))
    ) {
      return;
    }

    // const destDir = path.resolve(rootDir, 'node_modules', packageJson.name);
    // const distDir = path.resolve(file, '../dist');

    // if (fs.existsSync(destDir)) {
    //   fs.unlinkSync(destDir);
    // }

    // fs.symlinkSync(distDir, destDir);

    // console.log(
    //   chalk.green(
    //     `symlink ${destDir.substring(
    //       rootDir.length + 1
    //     )} -> ${distDir.substring(rootDir.length + 1)}`
    //   )
    // );
  });
};
