/**
 * handle save layout configuration on frontend dev server
 */
const fs = require('fs');
const os = require('os');
const path = require('path');
const { isEqual } = require('lodash');
const { execSync } = require('child_process');

function writeJsonFile(filename, data) {
  const newContent = JSON.stringify(data, null, '  ') + os.EOL;

  if (!fs.existsSync(path.dirname(filename))) {
    fs.mkdirSync(path.dirname(filename), { recursive: true });
  }

  if (fs.existsSync(filename)) {
    const oldContent = fs.readFileSync(filename, { encoding: 'utf-8' });
    if (oldContent === newContent) {
      return;
    }
  }

  fs.writeFileSync(filename, newContent, {
    encoding: 'utf-8'
  });

  console.log('Updated ' + filename);
}

function updateThemeVariantConfig(filename, data) {
  writeJsonFile(filename, data);
}

function updateDefaultTemplate(pageName, data) {
  const map = JSON.parse(
    fs.readFileSync('scripts/templates.map.json', { encoding: 'utf-8' })
  );

  let filename = map[pageName];

  if (!filename && data.pageNameAlt) {
    filename = map[data.pageNameAlt];
  }

  if (!filename) return;

  const oldJson = JSON.parse(fs.readFileSync(filename, { encoding: 'utf-8' }));

  // no change
  if (isEqual(oldJson[pageName], data)) return;

  console.log(`Updated ${filename}`);

  oldJson[pageName] = data;

  writeJsonFile(filename, oldJson);
}

function updateDefaultPage(pageName, data) {
  const map = JSON.parse(
    fs.readFileSync('scripts/pages.map.json', { encoding: 'utf-8' })
  );

  let filename = map[pageName];

  if (!filename && data.pageNameAlt) {
    filename = map[data.pageNameAlt];
  }

  if (!filename) return;

  const oldJson = JSON.parse(fs.readFileSync(filename, { encoding: 'utf-8' }));

  // no change
  if (isEqual(oldJson[pageName], data)) return;

  console.log(`Updated ${filename}`);

  oldJson[pageName] = data;

  writeJsonFile(filename, oldJson);
}

const AssetMaps = {
  pageLayouts: {
    to: 'assets/pages'
  },
  templates: {
    to: 'assets/templates'
  },
  noContentLayouts: {
    to: 'assets/no-contents'
  },
  gridLayouts: {
    to: 'assets/grids'
  },
  itemLayouts: {
    to: 'assets/items'
  },
  blockLayouts: {
    to: 'assets/blocks'
  }
};

function normalizeName(name) {
  return name.replace(/\S+/, '');
}

function saveLayout(payload, req, res) {
  const { files, theme: themeId } = payload;

  files.forEach(obj => {
    const { filename, name, content } = obj;

    const directory = filename.split('/src/').shift();

    if (AssetMaps[name]) {
      const folder = directory + '/src/' + AssetMaps[name].to;

      Object.keys(content).forEach(name => {
        const fname = name.toLowerCase().replace(/\W+/gim, '-');
        const newContent = content[name];
        newContent.info = {
          bundle: 'web',
          name
        };
        writeJsonFile(`${folder}/${fname}.json`, newContent);
      });
    }

    if (name === 'variant')
      Object.keys(content).forEach(name =>
        updateThemeVariantConfig(filename, content)
      );
  });

  execSync('yarn reload', {
    cwd: path.dirname(__filename)
  });

  res.writeHead(200, 'OK', { 'Content-Type': 'application/json' });
  res.write(JSON.stringify({ status: 'success', message: 'Save changes' }));
  res.end();
}

function saveVariant(payload, req, res) {
  const { files, theme: themeId } = payload;

  files.forEach(obj => {
    const { filename, name, content } = obj;

    writeJsonFile(filename, content);

    if (themeId == 'a0' || themeId == 'admincp') {
      if (name === 'variant')
        Object.keys(content).forEach(name =>
          updateThemeVariantConfig(filename, content)
        );
    }
  });

  res.writeHead(200, 'OK', { 'Content-Type': 'application/json' });
  res.write(JSON.stringify({ status: 'success', message: 'Save changes' }));
  res.end();
}

module.exports = {
  saveLayout,
  saveVariant
};
