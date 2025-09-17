require('../config/env');

const childProcess = require('child_process');
const path = require('path');
const { promisify } = require('util');
const rimraf = require('rimraf');

const exec = promisify(childProcess.exec);

function execBundle(bundle, packageDir) {
  const verbose = false;
  const env = {
    ...process.env,
    NODE_ENV: 'production',
    BABEL_ENV: bundle
  };
  const babelConfigPath = path.resolve(
    process.env.WORKBOX_ROOT,
    'babel.config.js'
  );
  const srcDir = path.resolve(packageDir, './src');
  const outDir = path.resolve(
    packageDir,
    'dist',
    {
      cjs: '.',
      esm: './esm',
      es: './es'
    }[bundle]
  );

  const command = [
    'yarn babel',
    '--config-file',
    babelConfigPath,
    '--extensions',
    '".js,.ts,.jsx,.tsx"',
    srcDir,
    '--out-dir',
    outDir,
    '--ignore',
    '"**/*.test.js","**/*.spec.ts","**/*.d.ts"'
  ].join(' ');

  if (verbose) {
    // eslint-disable-next-line no-console
    console.log(`running '${command}' with ${JSON.stringify(env)}`);
  }

  return exec(command, { env });
}

module.exports = async function babelBuildPackage(packageDir) {
  rimraf.sync(packageDir + '/dist/*');
  await execBundle('cjs', packageDir).catch(function (err) {
    console.error(err);
    process.exit(1);
  });
  await execBundle('esm', packageDir).catch(function (err) {
    console.error(err);
    process.exit(1);
  });
  await execBundle('es', packageDir).catch(function (err) {
    console.error(err);
    process.exit(1);
  });
};
