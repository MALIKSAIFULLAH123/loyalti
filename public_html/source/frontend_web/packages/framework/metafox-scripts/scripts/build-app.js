/**
 * Forward process call to multiple target
 */
const child_process = require('child_process');
const path = require('path');
const chalk = require('chalk');
const FileExtra = require('fs-extra');
const workingDir = path.resolve(process.cwd(), 'app');

function cleanupDistDirectory(options) {
  const distDir = path.resolve(workingDir, 'dist');
  FileExtra.emptyDirSync(distDir);
  FileExtra.ensureDirSync(distDir);
  FileExtra.copySync(path.resolve(workingDir, 'public'), distDir);
  FileExtra.ensureDirSync(path.resolve(distDir, 'admincp'));
}

const fs = require('fs');
const paths = require('../config/paths');

/**
 * @param {String} label
 * @param {child_process.ChildProcessByStdio} child
 */
function handleChild(label, child) {
  return new Promise((resolve, reject) => {
    [('SIGINT', 'SIGTERM')].forEach(sig => {
      process.on(sig, () => {
        if (!child.killed) {
          console.log(`terminating ${label}`);
          child.kill();
        }
      });
      child.on('exit', () => {
        console.log(`Build ${label} successfully.`);
        resolve();
      });

      child.on('error', () => {
        console.error(`Failed building ${label}.`);
        reject();
      });
    });
  });
}

function handleBuildProcess({ label, command, args, log, env }) {
  // start admin web server
  const child = child_process.spawn(command, args, {
    cwd: process.cwd(),
    env,
    stdio: log ? undefined : ['ignore', process.stdout, process.stderr]
  });

  if (log) {
    const stream = fs.createWriteStream(`${label}-out.log`);
    child.stdout.pipe(stream);
    child.stderr.pipe(stream);
  }

  return handleChild(label, child);
}

function buildApp(options) {
  const { profile } = options;
  const start = new Date().getTime();
  const paths = require('../config/paths');
  const appBuild = paths.appBuild;
  const distDir = path.resolve(workingDir, 'dist');
  const outputDir = process.env.MFOX_OUTPUT_DIR;

  const onFailure = async error => {
    console.log('build error');
  };

  const onComplete = async () => {
    fs.writeFileSync(`${appBuild}/bundle-success.log`, Date.now().toString(), {
      encoding: 'utf-8'
    });

    if (outputDir) {
      console.log(`Copy dist from ${distDir} to ${outputDir}`);
      if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
      }

      FileExtra.copySync(distDir, outputDir);
    }

    console.log('Build Successfully');
  };

  try {
    console.log(chalk.cyan('bundle packages'));
    cleanupDistDirectory(options);
    const spend = (new Date().getTime() - start) / 1000;
    console.log(chalk.cyan(`cleanup directory in ${spend}`));

    Promise.all([
      handleBuildProcess({
        command: 'yarn',
        args: [
          'metafox',
          'build:process',
          '--type=web',
          `--profile=${profile}`
        ],
        env: process.env,
        log: true,
        label: 'bundle-web'
      }),
      handleBuildProcess({
        command: 'yarn',
        args: [
          'metafox',
          'build:process',
          '--type=admincp',
          `--profile=${profile}`
        ],
        env: process.env,
        label: 'bundle-admincp',
        log: false
      })
    ])
      .then(onComplete)
      .catch(onFailure);
  } catch (err) {
    console.error(err.message);
  }
}

module.exports = buildApp;
