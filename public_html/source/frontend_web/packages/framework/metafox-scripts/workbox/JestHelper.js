const Workbox = require('./workbox');
const fs = require('fs');
const path = require('path');
function getRoots() {
  return Workbox.getPackageDirs().map(pattern =>
    `<rootDir>/${pattern}`.replace(/\*$/, '')
  );
}

function getModuleNameMapper() {
  const rootDir = Workbox.getRootDir();

  const mapper = {
    '\\.(jpg|jpeg|png|gif|eot|otf|webp|ttf|woff|woff2|mp4|webm|wav|mp3|m4a|aac|oga)$':
      '<rootDir>/tests/jest/__mock__/jest-file-transformer.js',
    '\\.(css|less|scss)$': 'identity-obj-proxy'
  };

  Workbox.getAllDevPackageJsonFiles().forEach(file => {
    const name = require(file).name;
    if (!fs.existsSync(path.resolve(file, '../src'))) {
      return;
    }
    const relativePath = path
      .resolve(file, '../src')
      .substring(rootDir.length + 1);

    mapper[`^${name}/(.*)$`] = `<rootDir>/${relativePath}/\$1`;
  });
  return mapper;
}

function getCollectCoverageFrom(opts) {
  if (opts.isWorkspace) {
    return [
      'src/**/*.{js,jsx,ts,tsx}',
      '!**/*/*.d.ts',
      '!**/*/types.ts',
      '!**/*/types/*.ts',
      '!**/*/Loadable.*',
      '!**.*/serviceWorker.ts'
    ];
  }
  return [
    `<rootDir>/${opts.relativePath}/src/**/*.{js,jsx,ts,tsx}`,
    `<rootDir>/${opts.relativePath}/src/*.{js,jsx,ts,tsx}`
  ];
}

function getTestMatch(opts) {
  if (opts.isWorkspace) {
    return [
      '**/__tests__|__specs__|tests|specs/**/*.[jt]s?(x)',
      '**/?(*.)+(spec|test).[tj]s?(x)'
    ];
  }
  return [
    `<rootDir>/${opts.relativePath}/**/__tests__|__specs__|tests|specs/**/*.[jt]s?(x)`,
    `<rootDir>/${opts.relativePath}/**/?(*.)+(spec|test).[tj]s?(x)`
  ];
}

module.exports = {
  getRootDir: Workbox.getRootDir,
  getTestMatch,
  getRoots,
  getModuleNameMapper,
  getCollectCoverageFrom
};
