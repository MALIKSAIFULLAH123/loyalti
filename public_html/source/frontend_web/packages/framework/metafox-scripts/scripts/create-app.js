//require
const { Workbox } = require('../workbox');
const fs = require('fs');
const chalk = require('chalk');
const path = require('path');
const {
  stripRootPath,
  scanStubs,
  transformAllStubs,
  isValidPackage
} = require('./transformStub');
const child_process = require('child_process');
const root = process.cwd();
const os = require('os');

const workingDir = process.cwd();

/**
 *
 * @param {String} package - add package settings. Example: "@company/note"
 * @return {true|false}  - Return true if package does not exists or otherwise
 */
function updateAppSettings(package) {
  const filename = path.resolve(root, 'app/settings.json');
  const settings = require(filename);
  const packagesAll = Object.keys(settings.packages);
  if (packagesAll.includes(package)) {
    return false;
  }
  // settings.packages.push(package);

  settings.packages[package] = 'local';

  fs.writeFileSync(filename, JSON.stringify(settings, null, '  ') + os.EOL, {
    encoding: 'utf-8'
  });

  console.log(chalk.cyan(`Updated ${stripRootPath(filename)}`));

  return true;
}

/**
 * Create new app
 *
 * @param {Object} argv
 */
function createApp(options) {
  const rootDir = Workbox.getRootDir();
  const [vendor, name] = options.package.split('/');
  const appRoot = path.resolve(
    rootDir,
    'packages',
    vendor.replace('@', ''),
    name
  );

  const package = options.package;

  // validate package name
  isValidPackage(package);

  const stubDir = 'create-app';

  fs.mkdirSync(appRoot, { recursive: true });

  const replacements = {
    $$NAME$$: name,
    $$VENDOR$$: vendor.replace('@', ''),
    $$PACKAGE$$: `@${package}`
  };

  const stubs = scanStubs(stubDir, {});

  transformAllStubs(options, stubDir, appRoot, stubs, replacements);

  console.log(
    chalk.cyan(`Created ${options.package} -> ./${stripRootPath(appRoot)}`)
  );

  const shouldBootstrap = updateAppSettings(`@${package}`);

  if (shouldBootstrap) {
    console.log(
      chalk.green(`------------------------------------------------`)
    );
    console.log(chalk.cyan(`Next Steps:`));
    console.log(chalk.cyan(`Run yarn bootstrap`));
    console.log(
      chalk.green(`------------------------------------------------`)
    );
  }
}

module.exports = createApp;
