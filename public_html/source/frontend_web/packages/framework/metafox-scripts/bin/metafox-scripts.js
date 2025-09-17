#!/usr/bin/env node

process.on('unhandledRejection', err => {
  throw err;
});

const yargs = require('yargs');
const { hideBin } = require('yargs/helpers');

const { profile, type, production } = yargs(hideBin(process.argv))
  .option('profile', { describe: 'build profile', type: 'string' })
  .option('type', {
    describe: 'build type: admincp, web, installation',
    type: 'string'
  })
  .option('production', {
    describe: 'development mode ?',
    type: 'boolean',
    default: false
  }).argv;

if (profile) {
  process.env.MFOX_BUILD_PROFILE = profile;
}

if (type) {
  process.env.MFOX_BUILD_TYPE = type;
}

if (production) {
  process.env.NODE_ENV = 'production';
  process.env.BABEL_ENV = 'production';
}

require('../config/env');

yargs(hideBin(process.argv))
  .command(
    'start',
    'Start development server',
    yargs => {
      return yargs
        .option('profile', { describe: 'build profile', type: 'string' })
        .option('type', {
          describe: 'build type: admincp, web, installation',
          type: 'string'
        })
        .option('production', {
          describe: 'development mode ?',
          type: 'boolean',
          default: false
        })
        .option('analyzer', {
          type: 'boolean',
          describe: 'run in analyzer mode in localhost:8888',
          default: undefined
        });
    },
    require('../scripts/start-dev')
  )
  .command(
    'fix',
    'Fix path',
    yargs => {
      return yargs;
    },
    require('../scripts/fix-workspace')
  )
  .command(
    'build',
    'Build production release',
    yargs => {
      return yargs.option('profile', {
        description: 'profile name',
        type: 'string'
      });
    },
    require('../scripts/build-app')
  )
  .command(
    'package:bundle [package]',
    'Bundle a package',
    yargs => {
      return yargs
        .option('publish', {
          describe: 'Publish to store ?',
          type: 'boolean',
          default: false
        })
        .option('target', {
          describe: 'Where to extract packages',
          type: 'string',
          default: false
        });
    },
    require('../scripts/bundle-package')
  )
  .command(
    'build:process',
    'Process build single task',
    yargs => {
      return yargs
        .option('dest', {
          describe: 'target build dir',
          type: 'string'
        })
        .option('profile', {
          description: 'profile name',
          type: 'string'
        })
        .option('type', {
          description: 'build type',
          type: 'string'
        })
        .option('upload', {
          describe: 'upload method',
          type: 'string'
        })
        .option('debug', {
          describe: 'upload method',
          type: 'boolean'
        });
    },
    require('../scripts/processBuildApp')
  )
  .command(
    'reload',
    'Reload project Settings',
    yargs => {
      return yargs
        .option('profile', {
          describe: 'build name',
          type: 'string'
        })
        .option('type', {
          describe: 'type=web, admincp, install',
          type: 'string'
        });
    },
    require('../scripts/reload')
  )
  .command(
    'create-app [package]',
    'Reload project Settings',
    yargs => {
      return yargs
        .positional('package', {
          describe: 'Package Name',
          type: 'string'
        })
        .option('overwrite', {
          describe: 'Overwrite existing file',
          type: 'boolean'
        });
    },
    require('../scripts/create-app')
  )
  .command(
    'publish',
    'publish to aws3',
    yargs => yargs,
    require('../scripts/publishSetupWizard')
  )
  .command(
    'bootstrap',
    'Bootstrap project',
    yargs => yargs,
    require('../scripts/bootstrap')
  )
  .command(
    'create-resource [package] [resource]',
    'Reload project Settings',
    yargs => {
      return yargs
        .positional('package', {
          describe: 'Package Name',
          type: 'string'
        })
        .positional('resource', {
          describe: 'Resource Name',
          type: 'string'
        })
        .option('overwrite', {
          describe: 'Overwrite existing file',
          type: 'boolean'
        });
    },
    require('../scripts/create-resource')
  )
  .command(
    'fix-workspace',
    'Fix project package paths',
    yargs => {
      return yargs;
    },
    require('../scripts/fix-workspace')
  )
  .command(
    'export',
    'Export source code',
    yargs => yargs,
    require('../scripts/export-source')
  )
  .parse();
