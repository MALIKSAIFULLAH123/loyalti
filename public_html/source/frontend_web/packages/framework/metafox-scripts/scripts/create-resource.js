//require
const { Workbox } = require('../workbox');
const fs = require('fs');
const chalk = require('chalk');
const path = require('path');
const { camelCase, upperFirst, toLower, lowerCase } = require('lodash');
const {
  transformAllStubs,
  stripRootPath,
  isValidPackage,
  scanStubs
} = require('./transformStub');
const { spawn } = require('child_process');
const workingDir = process.cwd();

function convertNameItem(str, type = 'default') {
  if (!str) return null;
  let result = camelCase(toLower(str));
  switch (type) {
    case 'studly':
      return upperFirst(result);
    case 'path':
      return lowerCase(result).replace(/\s+/g, '-').toLowerCase();
    default:
      return result;
  }
}

function createResource(options) {
  const rootDir = Workbox.getRootDir();
  const resource = convertNameItem(options.resource);
  const resourceStudly = convertNameItem(options.resource, 'studly');
  const resourcePath = convertNameItem(options.resource, 'path');

  isValidPackage(options.package);

  const package = `@${options.package}`;

  const [vendor, name] = options.package.split('/');

  const replacements = {
    $$NAME$$: name,
    $$VENDOR$$: vendor.replace('@', ''),
    $$PACKAGE$$: package,
    $$RESOURCE$$: resource,
    $$RESOURCE_STUDLY$$: resourceStudly,
    $$RESOURCE_PATH$$: resourcePath,
    '.tsx.stub': '.tsx'
  };

  const stubDir = 'create-resource';

  const stubs = scanStubs(stubDir, {
    blog: resource,
    Blog: resourceStudly
  });

  const appRoot = path.resolve(rootDir, 'packages', vendor, name);

  transformAllStubs(options, stubDir, appRoot, stubs, replacements);

  spawn('yarn', ['reload'], {
    cwd: workingDir,
    env: process.env,
    stdio: [process.stdin, process.stdout, process.stderr]
  });

  console.log(
    chalk.cyan(`Updated ${options.package} -> ./${stripRootPath(appRoot)}`)
  );
}

module.exports = createResource;
