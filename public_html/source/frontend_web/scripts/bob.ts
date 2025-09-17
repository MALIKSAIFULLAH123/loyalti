import fs from 'fs';
import path from 'path';
import glob from 'glob';

function copyLayouts() {
  const dirs = [
    'packages/metafox/admincp/src/theme',
    'packages/framework/metafox-installation/src/theme',
    'packages/framework/metafox-theme-default/src'
  ];

  const files = [
    'layout.templates',
    'layout.blocks',
    'layout.grids',
    'layout.items',
    'layout.noContents',
    'layout.pages',
    'layout.siteBlocks',
    'layout.siteDocks'
  ];

  dirs.forEach(dir => {
    files.forEach(file => {
      const from = path.join(process.cwd(), dir, file + '.json');
      const origin = path.join(process.cwd(), dir, file + '.origin.json');

      if (fs.existsSync(from)) {
        fs.writeFileSync(from, '{}', { encoding: 'utf-8' });
      }
      if (!fs.existsSync(origin)) {
        fs.copyFileSync(from, origin);
      }
    });
  });
}

copyLayouts();

const map = [
  {
    from: 'packages/**/*/*.noContents.origin.json',
    to: '/src/assets/no-contents'
  },
  {
    from: 'packages/**/*/layout.templates.origin.json',
    to: '/src/assets/templates'
  },
  {
    from: 'packages/**/*/layout.items.origin.json',
    to: '/src/assets/items'
  },
  {
    from: 'packages/**/*/layout.grids.origin.json',
    to: '/src/assets/grids'
  },
  {
    from: 'packages/**/*/layout.blocks.origin.json',
    to: '/src/assets/blocks'
  }
];

map.forEach(x => {
  glob
    .sync(x.from)
    .forEach((filename: string) => processLayoutFile(filename, x.to, {}));
});

function normalizeName(name: string) {
  return name
    .replace(/(\W+)/g, '-')
    .replace(/(-){2,}/g, '-')
    .replace(/-$/, '')
    .toLowerCase();
}

function writeToFile(dir, name, data) {
  const filename = path.join(dir, normalizeName(name) + '.json');

  fs.writeFileSync(filename, JSON.stringify(data, null, '  '), {
    encoding: 'utf-8'
  });
}

function processLayoutFile(
  filename: string,
  folder: string,
  info: Record<string, unknown> = {}
) {
  const dir = filename.split('/src/').shift() + folder;

  if (filename.includes('/admincp/')) {
    info.bundle = 'admincp';
  } else if (filename.includes('/metafox-installation/')) {
    info.bundle = 'installation';
  } else if (filename.includes('metafox-theme-default')) {
    info.bundle = 'web';
  }

  const data = JSON.parse(
    fs.readFileSync(filename, {
      encoding: 'utf-8'
    })
  );

  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }

  Object.keys(data).forEach(name => {
    const obj = data[name];
    if (!obj.info) {
      obj.info = {};
    }
    if (!obj.info.bundle) {
      obj.info.bundle = info.bundle;
    }

    if (!obj.info.name) {
      obj.info.name = name;
    }

    writeToFile(dir, name, obj);
  });
}
