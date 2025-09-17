const log = require('../helpers/log');
const path = require('path');
const FileExtra = require('fs-extra');

async function processBuildApp(options) {
  const { debug, profile, type } = options;
  process.env.NODE_ENV = debug ? 'development' : 'production';

  console.log(options);

  if (type) {
    process.env.MFOX_BUILD_TYPE = type;
  }

  if (profile) {
    process.env.MFOX_BUILD_PROFILE = profile;
  }

  log.heading('PROCESS BUILD APP');

  const { appBuild, appPublic } = require('../config/paths');

  FileExtra.copySync(appPublic, appBuild, {});

  log.info('MFOX_BUILD_TYPE=', process.env.MFOX_BUILD_TYPE);
  log.info('MFOX_BUILD_PROFILE=', process.env.MFOX_BUILD_PROFILE);

  require('./reload')({ profile, type });

  const configFactory = require('../config/webpack.config');

  const Webpack = require('webpack');

  log.info('Waiting for webpack bundle ...');

  const config = configFactory({
    webpackEnv: process.env.NODE_ENV,
    bundle: true
  });

  const compiler = Webpack(config);

  return new Promise((resolve, reject) => {
    compiler.run(function (err, stats) {
      if (err) {
        console.error(err);
        return reject(err);
      }
      return resolve({});
    });
  });
}

module.exports = processBuildApp;
