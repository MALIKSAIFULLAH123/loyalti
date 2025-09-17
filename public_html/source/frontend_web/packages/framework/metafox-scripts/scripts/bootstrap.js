const chalk = require('chalk');
const { spawnSync } = require('child_process');

/**
 * Bootstrap project
 */

function executeYarn(command, args, silent = false) {
  spawnSync(command, args, {
    env: process.env,
    cwd: process.cwd(),
    stdio: silent ? undefined : ['ignore', process.stdout, process.stderr]
  });
}

function bootstrap() {
  console.log(chalk.cyan('MetaFox Bootstrap ...'));
  executeYarn('yarn', ['lerna', 'info']);
  executeYarn('yarn', ['metafox', 'fix-workspace']);
  executeYarn('yarn', [
    'lerna',
    'bootstrap',
    '--loglevel=error',
    '--no-progress',
    '--force-local'
  ]);
  executeYarn('yarn', ['lerna', 'clean', '--loglevel=error', '--yes']);
  console.log(chalk.cyan('Link local packages ...'));
  executeYarn('yarn', ['lerna', 'link', '--force-local']);
}

module.exports = bootstrap;
