const { Workbox } = require('../workbox');
const path = require('path');
const fs = require('fs');

const availableScripts = [
  {
    name: 'unittest',
    override: false,
    cmd: function () {
      return 'metafox jest-unittest';
    }
  }
];

Workbox.getAllDevPackageJsonFiles().forEach(function (file) {
  const json = require(file);
  const dir = path.resolve(file, '../');
  if (!json.scripts) {
    json.scripts = {};
  }

  availableScripts.forEach(function (script) {
    const { name, override, cmd } = script;
    if (!name) throw new Error("script must contain 'name' property");
    if (!json.scripts[name] || override) {
      json.scripts[name] = cmd(json, dir);
    }
  });

  fs.writeFileSync(file, JSON.stringify(json, null, '  ') + '\n', {
    encoding: 'utf8'
  });
});
