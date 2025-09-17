/**
 * bundle and publish app to store
 */

const { get } = require('lodash');
const { dirname } = require('path');
const path = require('path');
const AdmZip = require('adm-zip');
var fs = require('fs');
const log = require('../helpers/log');
const chalk = require('chalk');
const numeral = require('numeral');
const FormData = require('form-data');
const axios = require('axios');
/**
 *
 * @param {*} dir
 * @param {*} files
 * @returns string[]
 */
function getFiles(dir, files) {
  if (!files) {
    files = [];
  }
  const dirs = fs.readdirSync(dir, { withFileTypes: true });
  for (const dirent of dirs) {
    const res = path.resolve(dir, dirent.name);
    if (dirent.isDirectory()) {
      getFiles(res, files);
    } else {
      files.push(res);
    }
  }
  return files;
}

function bundlePackage({ package: packageName, target: extractPath }) {
  const packageJson = require.resolve(`${packageName}/package.json`);

  if (!packageJson) {
    throw new Error('package not found ');
  }

  const { name, storeConfig, version } = require(packageJson);
  const reg = /^(\@?)(.+)\/(.+)$/;
  const filename = name.replace(reg, '$2-$3') + `-${version}.zip`;

  let productId = get(storeConfig, 'productId');
  if (!productId) {
    productId = packageName.replace('@', '');
  }

  const paths = {
    packageJson,
    filename,
    packageRoot: dirname(require.resolve(`${packageName}/package.json`)),
    root: process.cwd()
  };

  const prefixLength = paths.root.length + 1;
  paths.folder = paths.packageRoot.substring(prefixLength);

  const files = getFiles(paths.packageRoot);

  files.forEach(file => {
    const local = path.join(
      extractPath,
      'frontend',
      file.substring(prefixLength)
    );
    if (!fs.existsSync(dirname(local))) {
      fs.mkdirSync(dirname(local), { recursive: true });
    }
    log.info('Extracted to ', local);
    fs.copyFileSync(file, local);
  });

  log.heading('Extracted to ' + extractPath);
}

module.exports = bundlePackage;

// backend: metafox/activity
// frontend: @metafox/feed, @metafox/like, @metafox/share, @metafox/yup
