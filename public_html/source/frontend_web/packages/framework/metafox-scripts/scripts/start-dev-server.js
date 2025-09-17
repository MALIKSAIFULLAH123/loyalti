// Do this as the first thing so that any code reading it knows the right env.

process.env.BABEL_ENV = 'development';
process.env.NODE_ENV = 'development';

if (process.env.analyzer) {
  process.env.BABEL_ENV = 'production';
  process.env.NODE_ENV = 'production';
}

process.env.PUBLIC_URL = '';

// Makes the script crash on unhandled rejections instead of silently
// ignoring them. In the future, promise rejections that are not handled will
// terminate the Node.js process with a non-zero exit code.
process.on('unhandledRejection', err => {
  throw err;
});

// Ensure environment variables are read.
require('../config/env');
const fs = require('fs');
const chalk = require('chalk');
const Webpack = require('webpack');
const WebpackDevServer = require('webpack-dev-server');
const checkRequiredFiles = require('react-dev-utils/checkRequiredFiles');

const { prepareUrls } = require('react-dev-utils/WebpackDevServerUtils');

const openBrowser = require('react-dev-utils/openBrowser');
const paths = require('../config/paths');
const configFactory = require('../config/webpack.config');
const environments = require('../config/getClientEnvironment')(
  process.env,
  paths.publicUrlOrPath.slice(0, -1)
);
const isInteractive = process.stdout.isTTY;

// Warn and crash if required files are missing
if (!checkRequiredFiles([paths.appHtml, paths.appIndexJs])) {
  process.exit(1);
}

const PORT = parseInt(process.env.PORT, 10) || 3000;
const HOST = process.env.HOST || '127.0.0.1';

const proxy = fs.existsSync(paths.proxyJson)
  ? require(paths.proxyJson)
  : undefined;
const protocol = 'true' === process.env.HTTPS ? 'https' : 'http';

const urls = prepareUrls(
  protocol,
  HOST,
  PORT,
  paths.publicUrlOrPath.slice(0, -1)
);

console.log(urls);

const config = configFactory({
  webpackEnv: process.env.NODE_ENV,
  environments,
  paths,
  proxy,
  disableFirewall:
    !proxy || process.env.DANGEROUSLY_DISABLE_HOST_CHECK === 'true',
  allowedHosts: urls.lanUrlForTerminal,
  host: HOST,
  port: PORT
});

const compiler = Webpack(config);

const devServer = new WebpackDevServer(
  { ...config.devServer, open: false },
  compiler
);

// Launch WebpackDevServer.
devServer.startCallback(err => {
  if (err) {
    return console.log(err);
  }
  console.log(chalk.cyan('Starting the development server...\n'));
  openBrowser(urls.localUrlForBrowser);
});

[('SIGINT', 'SIGTERM')].forEach(function (sig) {
  process.on(sig, function () {
    devServer.close();
    process.exit();
  });
});

if ('true' !== process.env.CI) {
  // Gracefully exit when stdin ends
  process.stdin.on('end', function () {
    devServer.close();
    process.exit();
  });
}
