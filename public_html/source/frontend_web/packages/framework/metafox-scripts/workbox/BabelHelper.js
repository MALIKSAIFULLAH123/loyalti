const Workbox = require('./workbox');
const fs = require('fs');
const path = require('path');

function getModuleResolverAlias() {
  const alias = {};
  const rootDir = Workbox.getRootDir();
  Workbox.getAllDevPackages()
    .filter(absPath => {
      return (
        fs.existsSync(`${absPath}/package.json`) &&
        fs.existsSync(`${absPath}/src`)
      );
    })
    .forEach(function (absPath) {
      const name = require(`${absPath}/package.json`).name;
      const relPath = absPath.substr(rootDir.length + 1);
      alias[name] = path.resolve(absPath, 'src');
      alias[`${name}/*`] = path.resolve(absPath, 'src/*');
    });

  return alias;
}

module.exports = {
  getModuleResolverAlias
};
