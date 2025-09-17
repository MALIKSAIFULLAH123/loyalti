const child_process = require('child_process');
const path = require('path');

/**
 * start webpack dev server
 */
function startWeb(tag, env, output = true) {
  const proc = child_process.spawn(
    'node',
    [path.resolve(__dirname, 'start-dev-server.js')],
    {
      env,
      cwd: process.cwd(),
      stdio: output ? ['ignore', process.stdout, process.stderr] : undefined
    }
  );

  [('SIGINT', 'SIGTERM')].forEach(sig => {
    process.on(sig, () => {
      console.log(`terminating ${tag}`);
      proc.kill();
    });
  });
}

module.exports = function startDev(argv) {
  const { analyzer, type } = argv;
  require('./reload')(argv);

  if (type === 'installation') {
    startWeb('install', {
      ...process.env,
      MFOX_BUILD_TYPE: 'installation',
      analyzer: analyzer ? 1 : undefined
    });
    return;
  }

  if (type === 'admincp') {
    startWeb('admincp', {
      ...process.env,
      MFOX_BUILD_TYPE: 'admincp',
      analyzer: analyzer ? 1 : undefined
    });
    return;
  }

  startWeb('web', {
    ...process.env,
    MFOX_BUILD_TYPE: 'web',
    analyzer: analyzer ? 1 : undefined
  });

  if (!analyzer) {
    // startDev0();
  }
};
