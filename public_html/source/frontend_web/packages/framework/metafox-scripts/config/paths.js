require('../config/env');
const path = require('path');
const fs = require('fs');
const getPublicUrlOrPath = require('react-dev-utils/getPublicUrlOrPath');

const IS_ADMINCP = process.env.MFOX_BUILD_TYPE === 'admincp';
const IS_INSTALLATION = process.env.MFOX_BUILD_TYPE === 'installation';
const IS_DEV = process.env.NODE_ENV !== 'production';
const workbox = require('../workbox/workbox');

const appDirectory = path.join(process.cwd(), 'app');
const resolveApp = relativePath => path.resolve(appDirectory, relativePath);

const appPackageJson = resolveApp('package.json');
// @risk:  could not build admin
// const publicUrlOrPath = getPublicUrlOrPath(process.env.PUBLIC_URL);

let publicUrlOrPath = IS_DEV
  ? '/'
  : getPublicUrlOrPath(IS_DEV, undefined, process.env.PUBLIC_URL);

if (IS_ADMINCP && !IS_DEV) {
  publicUrlOrPath = `${publicUrlOrPath}admincp/`;
}

if (IS_INSTALLATION && !IS_DEV) {
  // publicUrlOrPath = `${publicUrlOrPath}install/`;
}

const moduleFileExtensions = [
  'web.mjs',
  'mjs',
  'web.js',
  'js',
  'web.ts',
  'ts',
  'web.tsx',
  'tsx',
  'json',
  'web.jsx',
  'jsx'
];

// Resolve file paths in the same order as webpack
const resolveModule = (resolveFn, filePath) => {
  const extension = moduleFileExtensions.find(extension =>
    fs.existsSync(resolveFn(`${filePath}.${extension}`))
  );

  if (extension) {
    return resolveFn(`${filePath}.${extension}`);
  }

  return resolveFn(`${filePath}.js`);
};

const wsPath = path.resolve(workbox.getRootDir());

const appHtml = resolveApp(
  process.env.MFOX_APP_HTML ?? 'public/index.ejs.html'
);

const appPublic = resolveApp(process.env.MFOX_APP_PUBLIC ?? 'public');

function getAppIndexJs() {
  if (IS_ADMINCP) {
    return resolveModule(resolveApp, 'src/admincp');
  }

  if (IS_INSTALLATION) {
    return resolveModule(resolveApp, 'src/install');
  }

  return resolveModule(resolveApp, 'src/index');
}

const appIndexJs = getAppIndexJs();

const appBuild = resolveApp(`dist${publicUrlOrPath}`);

const proxyFile = process.env.MFOX_PROXY_FILE ?? 'proxy.json';
const proxyJson = resolveApp(proxyFile);

const appSettingJson = resolveApp(
  process.env.MFOX_SETTING_JSON ?? 'settings.json'
);

// config after eject: we're in ./config/
module.exports = {
  wsPath,
  publicPath: IS_INSTALLATION ? 'auto' : publicUrlOrPath,
  wsNodeModules: path.resolve(wsPath, 'node_modules'),
  wsPackages: path.resolve(wsPath, 'packages'),
  dotenv: resolveApp(process.env.MFOX_ENV_FILE ?? '.env'),
  appPath: resolveApp('.'),
  appBuild,
  appPublic,
  appHtml,
  appIndexJs,
  appPackageJson,
  appSettingJson,
  proxyJson,
  appSrc: resolveApp('src'),
  appTsConfig: resolveApp('tsconfig.json'),
  appJsConfig: resolveApp('jsconfig.json'),
  yarnLockFile: resolveApp('yarn.lock'),
  testsSetup: resolveModule(resolveApp, 'src/setupTests'),
  proxySetup: resolveApp('src/setupProxy.js'),
  appNodeModules: resolveApp('node_modules'),
  swSrc: resolveModule(resolveApp, 'src/service-worker'),
  publicUrlOrPath,
  moduleFileExtensions
};
