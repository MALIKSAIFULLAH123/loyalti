const { Workbox } = require('../workbox');
const fs = require('fs');
const path = require('path');
const log = require('../helpers/log');
const paths = require('../config/paths');
const glob = require('glob');
const getCommentedInfo = require('../helpers/getCommentedInfo');
const os = require('os');
const pathToReg = require('path-to-regexp');
const SILENT = !!process.env.MFOX_BUILD_SERVICE;
const { intersection, mergeWith } = require('lodash');

function mergeIntergration(objValue, srcValue) {
  if (isArray(objValue)) {
    return objValue.concat(srcValue);
  }
}

const AssetMaps = [
  {
    from: '**/assets/no-contents/*.json',
    type: 'assets.noContents',
    to: 'layout.noContents.origin.json'
  },
  {
    from: `**/assets/pages/*.json`,
    type: 'layout',
    to: 'layout.pages.origin.json',
    deduce: true
  },
  {
    from: '**/assets/templates/*.json',
    type: 'template',
    to: 'layout.templates.origin.json'
  },
  {
    from: '**/assets/items/*.json',
    type: 'assets.items',
    to: 'layout.items.origin.json'
  },
  {
    from: '**/assets/grids/*.json',
    type: 'assets.grids',
    to: 'layout.grids.origin.json'
  },
  {
    from: '**/assets/blocks/*.json',
    type: 'assets.blocks',
    to: 'layout.blocks.origin.json'
  },
  {
    from: '**/assets/intergration/*.json',
    type: 'assets.intergration',
    to: 'layout.intergration.origin.json'
  }
];
const {
  isPlainObject,
  trim,
  uniqBy,
  filter,
  flatten,
  get,
  isArray,
  sortBy,
  isEqualWith
} = require('lodash');

const PACKAGES = [];

const basePackages = {
  '@metafox/framework': 'local',
  '@metafox/local-store': 'local',
  '@metafox/cookie': 'local',
  '@metafox/toast': 'local',
  '@metafox/json2yup': 'local',
  '@metafox/constants': 'local',
  '@metafox/dialog': 'local',
  '@metafox/form': 'local',
  '@metafox/form-elements': 'local',
  '@metafox/html-viewer': 'local',
  '@metafox/intl': 'local',
  '@metafox/ui': 'local',
  '@metafox/jsx': 'local',
  '@metafox/layout': 'local',
  '@metafox/normalization': 'local',
  '@metafox/rest-client': 'local',
  '@metafox/route': 'local',
  '@metafox/theme-default': 'local',
  '@metafox/admincp': 'local',
  '@metafox/utils': 'local',
  '@metafox/echo': 'local',
  '@metafox/lexical': 'local'
};

const validViewTypes = [
  'block',
  'itemView',
  'dialog',
  'skeleton',
  'siteDock',
  'siteFixedDock',
  'ui',
  'embedView',
  'formElement',
  'popover'
];

const validTypes = [
  ...validViewTypes,
  'themeProcessor',
  'route',
  'modalRoute',
  'saga',
  'theme',
  'reducer',
  'message',
  'service',
  'packages',
  'dependency',
  'layoutBlockFeature',
  'admincp.message',
  'mockService',
  'theme.style.editor',
  'theme.styles'
];

// loadable by type component.
const loadableByTypeMap = {
  block: true,
  skeleton: true,
  itemView: false,
  dialog: true,
  ui: true,
  embedView: true,
  formElement: true,
  route: true,
  modalRoute: false,
  siteDock: true,
  siteFixedDock: true,
  saga: false,
  reducer: false,
  service: false,
  message: false,
  package: false,
  dependency: false,
  'theme.style.editor': true // don't be loadable lib.
};
const chunkByTypeMap = {
  dialog: 'dialogs',
  route: 'routes',
  siteDock: 'boot',
  siteFixedDock: 'boot',
  modalRoute: 'modalRoutes'
};

function getPackageDir(packageName) {
  try {
    return path.dirname(require.resolve(`${packageName}/package.json`));
  } catch (error) {
    return null;
  }
}

const trimRoot = str => str.substring(Workbox.getRootDir().length + 1);

function mixFrom(packageName, file) {
  return `${packageName}/${file.replace(/.\w+$/, '')}`;
}

function mixSource(packageDir, file) {
  return path.resolve(packageDir, 'src', file);
}

function isValidType(type) {
  if (!type) return false;
  return validTypes.includes(type);
}

function bundleSinglePackage(bundle, packageName, bundleType, acceptThemes) {
  if (!SILENT) log.info('Analyzing', packageName);

  const dir = getPackageDir(packageName);

  if (!dir) return;

  const packageInfo = require(`${packageName}/package.json`);

  const theme = get(packageInfo, 'metafox.theme');
  let allowAssets = true;

  if (theme) {
    if (!acceptThemes || !acceptThemes.includes(theme)) {
      allowAssets = false;
    }
  }

  function checkScriptFile(filename) {
    const source = mixSource(dir, filename);
    const info = getCommentedInfo(source);
    if (info && isValidType(info['@type'])) {
      const type = info['@type'];
      delete info['@type'];
      info['theme'] = info['theme'] ?? theme;
      info['depends'] = info.depends
        ? info.depends.split(',').map(x => x.trim())
        : undefined;

      // if belong is not required
      if (info.depends && !intersection(info.depends, PACKAGES).length) {
        return;
      }

      bundle.push({
        info,
        from: mixFrom(packageName, filename),
        source,
        packageName,
        type
      });
    }
  }

  /**
   * @check layout file
   */
  function checkJsonFile(type, filename, deduce) {
    const source = mixSource(dir, filename);
    const readFile = fs.readFileSync(source, { encoding: 'utf-8' });
    let data;
    try {
      data = JSON.parse(readFile);
    } catch (err) {
      console.log(`JSON File Error: ${readFile}`);
    }

    // fix before v5.1.3 compatible.
    if (deduce && Object.keys(data).length === 1) {
      const name = Object.keys(data).shift();
      data = data[name];
      if (!data.info?.name) {
        data.info.name = name;
      }
      console.log(`DEDUCE ${name}: ` + filename);
      // update json file.
      writeToFile(source, data);
    }

    const info = data.info ?? {};
    info.theme = info.theme ?? theme;

    bundle.push({
      info: data.info ?? {},
      source,
      packageName,
      type
    });
  }

  glob.sync('**/*.ts', { cwd: `${dir}/src` }).forEach(checkScriptFile);
  glob.sync('**/*.tsx', { cwd: `${dir}/src` }).forEach(checkScriptFile);

  if (allowAssets) {
    AssetMaps.forEach(x => {
      glob
        .sync(x.from, { cwd: `${dir}/src` })
        .forEach(filename => checkJsonFile(x.type, filename, x.deduce));
    });
  }

  // check file name
  const messages = mixSource(dir, 'messages.json');

  if (fs.existsSync(messages)) {
    bundle.push({
      source: messages,
      packageName,
      type: 'message',
      info: {}
    });
  }

  const adminMessages = mixSource(dir, 'admincp.messages.json');

  if (fs.existsSync(adminMessages)) {
    bundle.push({
      source: adminMessages,
      packageName,
      type: 'message',
      info: {
        bundle: 'admincp'
      }
    });
  }
}

function capitalizeWord(s) {
  return s.substring(0, 1).toUpperCase() + s.substring(1);
}

function capitalizeCase(name) {
  return name
    ? name.replace('_', '.').split(/\W+/g).map(capitalizeWord).join('')
    : 'undefinedName';
}

function camelCase(name) {
  return name
    .replace('_', '.')
    .split(/\W+/g)
    .map((x, index) => (index > 0 ? capitalizeWord(x) : x))
    .join('');
}

function fixRoutePath(path) {
  // console.log('testPath', path);
  const arr = path
    .split(',')
    .map(x => x.trim())
    .filter(Boolean)
    .filter(path => pathToReg.match(path))
    .map(x => '/' + x.replace(/(^\/|\/$)/g, ''));
  return arr.length > 1 ? arr : arr.pop();
}

function fixBooleanValue(end, defaultValue) {
  switch (end) {
    case '0':
      return false;
    case 'false':
      return false;
    case 'no':
      return false;
    default:
      return defaultValue;
  }
}

function fixRoutePriority(priority, index) {
  const x = priority ? parseInt(priority, 10) : index;
  return x != NaN ? x : index;
}

function bundleAllPackages(bundle, packages, bundleType) {
  packages.forEach(packageName => {
    bundleSinglePackage(bundle, packageName, bundleType);
  });
}

function outputPath(fullPath) {
  const rootDir = Workbox.getRootDir();
  return '.' + fullPath.substring(rootDir.length);
}

function chunkSingleTheme(theme, originBundle, bundleType, dirname) {
  const { name } = theme.info;

  chunkAllViews(
    {},
    originBundle,
    path.resolve(dirname, `theme.${theme.info.name}.views.tsx`),
    bundleType,
    theme.info.name
  );

  const bundle = JSON.parse(JSON.stringify(originBundle));

  bundleSinglePackage(bundle, theme.packageName, bundleType, [theme.info.name]);

  const dir = path.dirname(theme.source).substring(process.cwd().length + 1);

  const names = [
    'layout.templates',
    'layout.blocks',
    'layout.grids',
    'layout.items',
    'layout.noContents',
    'layout.pages',
    'layout.siteBlocks',
    'layout.siteDocks',
    'layout.intergration',
    'layout.siteFixedDocks'
  ];

  names.forEach(x => {
    if (!fs.existsSync(`${dir}/${x}.origin.json`)) {
      if (fs.existsSync(`${dir}/${x}.json`)) {
        fs.copyFileSync(`${dir}/${x}.json`, `${dir}/${x}.origin.json`);
      } else {
        writeToFile(`${dir}/${x}.json`, '{}');
        writeToFile(`${dir}/${x}.origin.json`, '{}');
      }
    }
    if (!fs.existsSync(`${dir}/${x}.json`)) {
      fs.writeFileSync(`${dir}/${x}.json`, '{}', {
        encoding: 'utf-8'
      });
    }
  });

  AssetMaps.forEach(asset => {
    writeToFile(
      `${dir}/${asset.to}`,
      getAssetForTheme(asset.type, bundle, bundleType, name).data
    );
  });

  const { data: siteDocks } = getSiteDockForTheme(bundle, bundleType, name);

  writeToFile(`${dir}/layout.siteDocks.origin.json`, siteDocks);

  const { data: siteFixedDocks } = getSiteFixedDockForTheme(
    bundle,
    bundleType,
    name
  );

  writeToFile(`${dir}/layout.siteFixedDocks.origin.json`, siteFixedDocks);

  names.forEach(x => {
    minimizeLayoutFile(`${dir}/${x}.origin.json`, `${dir}/${x}.json`);
  });

  const info = Object.assign({}, theme.info, { id: name, dir });

  const source = `/* eslint-disable */
import theme from '${theme.from}';

export { views } from './theme.${name}.views';
export const info = ${JSON.stringify(info)};

export default theme;
    `;
  // console.log('chunk theme package', theme.packageName);
  const destination = path.resolve(dirname, `theme.${name}.tsx`);

  writeToFile(destination, source);
}

/**
 * Chunk all theme resource to single one.
 * Theme must be recovery in to single one.
 * @param stats
 * @param pages
 * @param bundle
 * @param dirname
 * @param bundleType
 */
function chunkThemes(stats, bundle, bundleType, dirname) {
  const themes = bundle.filter(
    x => x.type === 'theme' && x.info.bundle == bundleType
  );

  stats.themes = Object.keys(themes).length;

  themes.forEach(theme => chunkSingleTheme(theme, bundle, bundleType, dirname));
}

// trim custom if custom is the same origin
function minimizeLayoutFile(originFile, customFile) {
  const origin = JSON.parse(fs.readFileSync(originFile, { encoding: 'utf-8' }));
  const custom = JSON.parse(fs.readFileSync(customFile, { encoding: 'utf-8' }));

  let changed = false;
  Object.keys(custom).forEach(name => {
    if (origin[name] && isEqualWith(custom[name], origin[name])) {
      delete custom[name];
      changed = true;
    }
  });

  if (changed) {
    fs.writeFileSync(customFile, JSON.stringify(custom, null, '  '), {
      encoding: 'utf-8'
    });
  }
}

/**
 * Chunk all theme resource to single one.
 * Theme must be recovery in to single one.
 * @param stats
 * @param bundle
 * @param dirname
 * @param bundleType
 */
function chunkThemeStyles(stats, bundle, dirname, bundleType) {
  const themeStyles = bundle
    .filter(x => x.type === 'theme.styles')
    .filter(x => !x.info.bundle || x.info.bundle === bundleType);

  stats.styles = Object.keys(themeStyles).length;

  themeStyles.forEach(style => {
    const dir = path.dirname(style.source).substring(process.cwd().length + 1);
    const { name, theme } = style.info;
    const id = theme + ':' + name;
    const info = Object.assign({}, style.info, { id, dir });

    const source = `/* eslint-disable */

import style from '${style.from}';

export const info = ${JSON.stringify(info)};

export default style;
    `;
    // console.log('chunk theme package', theme.packageName);

    const destination = path.resolve(
      dirname,
      `style.${name}.theme.${theme}.tsx`
    );

    writeToFile(destination, source);
  });
}

function chunkMessages(stats, bundle, destination, bundleType = false) {
  let data = {};

  bundle
    .filter(x => x.type === 'message')
    .forEach(x => {
      data = Object.assign(data, require(x.source));
    });

  if (bundleType) {
    bundle
      .filter(x => x.type === 'message')
      .filter(x => x.info.bundle === undefined || x.info.bundle === bundleType)
      .forEach(x => {
        data = Object.assign(data, require(x.source));
      });
  }

  stats.messages = Object.keys(data).length;

  writeToFile(destination, data);
}

function chunkMessageJs(destination) {
  const source = filterChunkedOutput(`
import messages from './messages.json';

  ${injectConfigSource('messages')}`);

  writeToFile(destination, source);
}

function discoverElements(name, types, stats, bundle, basePath) {
  const items = collectBundleItems(bundle, types, capitalizeCase);

  const blockFeatures = toLoadableObject(name, items, x => `[${x.importName}]`);

  stats[name] = items.length;

  const source = `${filterChunkedOutput(blockFeatures)}
// inject to config
export default ${name};
`;

  const destination = path.resolve(basePath, name + '.ts');

  writeToFile(destination, source);
}

function toLoadableJSON(items, processItem) {
  return items.reduce((acc, x) => {
    const obj = processItem(x);
    if (obj) acc[x.info.name] = obj;
    return acc;
  }, {});
}

function chunkPageLayouts(bundle, bundleType) {
  let pageFileMap = {};
  let pages = {};
  // write down to /dev0/pageFileMap

  bundle
    .filter(x => x.type === 'layout')
    .filter(x => x.info.bundle === bundleType)
    .forEach(x => {
      const filename = trimRoot(x.source);
      const config = require(x.source);
      Object.keys(config).forEach(page => {
        pageFileMap[page] = filename;
      });
      pages = Object.assign(pages, config);
    });

  writeToFile(Workbox.getRootDir() + '/scripts/pages.map.json', pageFileMap);

  return pages;
}

function getPageLayoutForTheme(bundle, bundleType, themeName) {
  let fileMap = {};
  let data = {};
  // write down to /dev0/pageFileMap

  bundle
    .filter(x => x.type === 'layout')
    .filter(x => x.info.bundle === bundleType)
    .filter(x => x.info.theme == undefined || x.info.theme == themeName)
    .forEach(x => {
      const filename = trimRoot(x.source);
      const config = require(x.source);
      Object.keys(config).forEach(page => {
        fileMap[page] = filename;
      });
      data = Object.assign(data, config);
    });

  return {
    data,
    fileMap
  };
}

function getAssetForTheme(type, bundle, bundleType, themeName) {
  let fileMap = {};
  let data = {};
  // write down to /dev0/pageFileMap

  bundle
    .filter(x => x.type === type)
    .filter(x => x.info.bundle === bundleType || x.info.bundle === undefined)
    .filter(x => x.info.theme === undefined || x.info.theme == themeName)
    .forEach(x => {
      const filename = trimRoot(x.source);

      const name =
        x.info.name ?? path.basename(filename).replace(/\.json$/, '');
      if (type === 'assets.intergration') {
        data[name] = mergeWith(
          {},
          data[name],
          require(x.source),
          mergeIntergration
        );
      } else {
        data[name] = require(x.source);
      }
    });

  return {
    data,
    fileMap
  };
}

function getSiteDockForTheme(bundle, bundleType, themeName) {
  let data = {};

  bundle
    .filter(x => x.type === 'siteDock')
    .filter(x => x.info.bundle === undefined || x.info.bundle === bundleType)
    .filter(x => x.info.theme === undefined || x.info.theme === themeName)
    .forEach(x => {
      data[x.info.name] = true;
    });

  return { data };
}

function getSiteFixedDockForTheme(bundle, bundleType, themeName) {
  let data = {};

  bundle
    .filter(x => x.type === 'siteFixedDock')
    .filter(x => x.info.bundle === undefined || x.info.bundle === bundleType)
    .filter(x => x.info.theme === undefined || x.info.theme === themeName)
    .forEach(x => {
      data[x.info.name] = true;
    });

  return { data };
}

function normalizeBlockInfo({ info }) {
  if (!info.title) return false;

  const obj = {
    title: info.title,
    description: info.description ?? '',
    keywords: info.keywords
      ? info.keywords.split(',').map(trim).filter(Boolean)
      : [],
    bundleType: info.bundle
  };

  return obj;
}

function chunkBlockInfo(stats, bundle, destination, bundleType) {
  const items = collectBundleItems(bundle, ['block'], capitalizeCase);

  const blocks = toLoadableJSON(items, x => ({
    ...x.info,
    admincp: x.info.admincp ?? x.info.bundle === 'admincp'
  }));

  // skip overload

  const source = filterChunkedOutput(`
import blockFeatures from './blockFeatures';

  const info = {
    blocks: ${JSON.stringify(blocks, null, '  ')},
    blockFeatures,
  }
  
  export default info;
`);

  writeToFile(destination, source);
}

/**
 * @example injectConfigSource('routes', 'models')
 * @param {string} parameters
 * @return string
 */
function injectConfigSource(parameters) {
  const body = parameters
    .split(',')
    .map(name => name.trim())
    .map(name => `config.${name}=${name};`)
    .join('\n    ');
  return `export default function injector(config: any) {
    ${body}
}`;
}

function prepareImports(items) {
  return items
    .map(({ info, type, from, importName }) => {
      // should loadable or chunk?
      const lazy =
        (!!loadableByTypeMap[type] || info.chunkName) && info.lazy != false;

      const chunkName = info.chunkName ? info.chunkName : chunkByTypeMap[type];
      const chunkSyntax = chunkName
        ? `/* webpackChunkName: "${chunkName}" */`
        : '';

      if (lazy) {
        return `
const ${importName} = loadable(() => import(${chunkSyntax} '${from}'));`;
      }

      return `import ${importName} from '${from}';`;
    })
    .join('\n');
}

function filterChunkedOutput(content) {
  if (content.indexOf('loadable(') > 0 || content.indexOf('React.lazy') > 0) {
    content = `import loadable from '@loadable/component'; 

${content}`;
  }

  return `/* eslint-disable */
${content}`;
}

function toLoadableArray(exportName, items, processItem) {
  const processItems = flatten(items.map(processItem));

  let content = JSON.stringify(processItems, null, '  ');

  let imports = prepareImports(items);

  items.forEach(({ importName }) => {
    content = content
      .replaceAll(`"[${importName}]"`, importName)
      .replaceAll(`"<[${importName}] />"`, `<${importName} />`);
  });

  return `${imports} 
   const ${exportName} = ${content};`;
}

function toLoadableObject(exportName, items, processItem) {
  let data = JSON.stringify(
    uniqBy(items, 'info.name').reduce((acc, x) => {
      acc[x.info.name] = processItem(x);
      return acc;
    }, {}),
    null,
    '  '
  );

  let imports = prepareImports(uniqBy(items, 'info.name'));

  items.forEach(({ importName }) => {
    data = data.replace(`"[${importName}]"`, importName);
  });

  return `${imports} 

// export 
export const ${exportName} = ${data};`;
}

function collectBundleItems(
  bundle,
  includeTypes,
  naming,
  bundleType,
  callback,
  theme
) {
  return Object.values(
    bundle
      .filter(x => includeTypes.includes(x.type))
      .filter(x => {
        return (
          (x.info.bundle === undefined ||
            bundleType === undefined ||
            x.info.bundle === bundleType) &&
          x.info.theme == theme
        );
      })
      .reduce((acc, x) => {
        x.importName = naming(x.info.name);
        acc[x.info.name] = x;
        return acc;
      }, {})
  )
    .map((x, index) => ({
      ...x,
      priority: fixRoutePriority(x.info.priority, index)
    }))
    .filter(callback ?? Boolean)
    .sort((a, b) => a.priority - b.priority);
}

function chunkRoutes(stats, bundle, destination, bundleType) {
  const processItem = ({ info: { name, path, end }, importName }) => {
    const fixedPath = fixRoutePath(path);
    if (isArray(fixedPath)) {
      return fixedPath.map((p, index) => ({
        component: `[${importName}]`,
        path: p,
        name
      }));
    }
    return {
      component: `[${importName}]`,
      path: fixedPath,
      name
    };
  };

  const popoverHandlers = {};

  bundle
    .filter(x => x.type === 'popover') // skip popover
    .filter(x => x.info.path)
    .forEach(x => {
      popoverHandlers[x.info.name] = {
        component: x.info.name,
        path: fixRoutePath(x.info.path)
      };
    });

  const modalItems = collectBundleItems(
    bundle,
    ['modalRoute'],
    capitalizeCase,
    bundleType
  );

  let routeItems = collectBundleItems(
    bundle,
    ['route'],
    capitalizeCase,
    bundleType
  );

  stats.routes = routeItems.length;

  stats.modals = modalItems.length;

  const routes = toLoadableArray('routes', routeItems, processItem);

  const modals = toLoadableArray('modals', modalItems, processItem);

  const source = filterChunkedOutput(`
/* export routes */
${routes}

/* export modal */
${modals}

/* export popoverHandlers*/
const popoverHandlers  = ${JSON.stringify(
    Object.values(popoverHandlers),
    null,
    '  '
  )}
  ${injectConfigSource('routes, modals, popoverHandlers')}`);

  writeToFile(destination, source);
}

function chunkAllViews(stats, bundle, destination, bundleType, theme) {
  const viewItems = collectBundleItems(
    bundle,
    validViewTypes,
    capitalizeCase,
    bundleType,
    null,
    theme
  );

  const views = toLoadableObject('views', viewItems, x => `[${x.importName}]`);

  stats.views = viewItems.length;

  const source = `${filterChunkedOutput(views)}
// inject to config
${injectConfigSource('views')}`;

  writeToFile(destination, source);
}

function chunkAllServices(stats, bundle, destination) {
  const named = 'services';
  const serviceItems = collectBundleItems(bundle, ['service'], capitalizeCase);
  const services = toLoadableObject(
    named,
    serviceItems,
    x => `[${x.importName}]`
  );

  stats.services = serviceItems.length;
  const source = `
${filterChunkedOutput(services)}

// inject to config
${injectConfigSource(named)}`;

  writeToFile(destination, source);
}

function chunkAllMockedService(stats, bundle, destination) {
  const named = 'mockServices';

  const mainServices = collectBundleItems(
    bundle,
    ['service'],
    capitalizeCase,
    undefined
  );

  const mockServices = collectBundleItems(
    bundle,
    ['mockService'],
    capitalizeCase
  ).concat(mainServices);

  const services = toLoadableObject(
    named,
    mockServices,
    x => `[${x.importName}]`
  );

  stats.services = mockServices.length;
  const source = `
// THIS IS DEFAULT SERVICE FOR RUN TESTING.
// PLEASE DO NOT IMPORT INTO OTHER PLACE.

${filterChunkedOutput(services)}

// inject to config
export default ${named}
`;

  writeToFile(destination, source);
}

function chunkProduce(stats, bundle, destination, bundleType) {
  // remove duplicated.

  const sagaItems = collectBundleItems(
    bundle,
    ['saga'],
    name => camelCase(name) + 'Saga',
    bundleType
  );
  const reducerItems = collectBundleItems(
    bundle,
    ['reducer'],
    name => camelCase(name) + 'Reducer',
    bundleType
  );
  const sagas = toLoadableArray('sagas', sagaItems, x => `[${x.importName}]`);

  const reducers = toLoadableObject(
    'reducers',
    reducerItems,
    x => `[${x.importName}]`
  );

  const source = filterChunkedOutput(`
  ${sagas}
  ${reducers}
  ${injectConfigSource('reducers,sagas')}
  `);

  stats.reducers = reducerItems.length;
  stats.sagas = sagaItems.length;

  writeToFile(destination, source);
}

function writeToFile(filename, source) {
  const content = isPlainObject(source)
    ? JSON.stringify(source, null, '  ')
    : source;

  log.info('Updated', outputPath(filename));

  fs.writeFileSync(filename, content + os.EOL, { encoding: 'utf-8' });
}

function chunkSettingFile(origin, destination) {
  const config = JSON.parse(JSON.stringify(origin));
  delete config.packages;

  const source = `/* eslint-disable */
const root = ${JSON.stringify(config, null, '  ')}

// inject root
${injectConfigSource('root')}`;
  writeToFile(destination, source);
}

function chunkApp(name, bundle, filename) {
  const source = `
import $x2 from '${name}/${bundle}/settings';
import $x3 from '${name}/${bundle}/services';
import $x4 from '${name}/${bundle}/routes';
import $x5 from '${name}/${bundle}/message';
import $x6 from '${name}/${bundle}/views';
import $x7 from '${name}/${bundle}/produce';

const bundle = [$x2, $x3, $x4, $x5, $x6, $x7].reduce((acc, value) => {
  value(acc);

  return acc;
}, {});

export default bundle;
`;
  writeToFile(filename, source);
}

function chunkThemeSnippetEditor(bundle, destination, bundleType) {
  const processItem = ({ info, importName }) => {
    return {
      ...info,
      component: `[${importName}]`
    };
  };

  let items = collectBundleItems(
    bundle,
    ['theme.style.editor'],
    capitalizeCase,
    bundleType
  );

  let themeSnippets = toLoadableArray('themeSnippets', items, processItem);

  // reorder theme snippet

  const source = filterChunkedOutput(`
/* export themeSnippets */
${themeSnippets}

export default themeSnippets;
`);

  writeToFile(path.resolve(destination, 'theme.style.editor.tsx'), source);
}

function bundleFor({ siteName, bundleDir, bundleType }) {
  const mode = process.env.NODE_ENV || 'production';
  // console.log(chalk.green('Analyzing configuration ...'));

  const settingFile = paths.appSettingJson;
  const bundle = [];
  const rootDir = Workbox.getRootDir();
  const siteDir = path.resolve(rootDir, 'app');
  const settings = require(settingFile);
  const packages = { ...basePackages, ...settings.packages };

  Object.keys(packages).forEach(package => {
    PACKAGES.push(package);
  });

  const stats = {};

  const createPath = fileName =>
    path.resolve(rootDir, siteDir, bundleDir, fileName);

  const discoverPath = createPath('');

  if (!fs.existsSync(discoverPath)) {
    fs.mkdirSync(discoverPath);
  }

  bundleAllPackages(bundle, PACKAGES, bundleType);
  // summary stats

  discoverElements(
    'blockFeatures',
    ['layoutBlockFeature'],
    stats,
    bundle,
    discoverPath
  );

  chunkPageLayouts(bundle, bundleType, 'pages.json');

  chunkApp(
    siteName,
    `bundle-${bundleType}`,
    createPath('config.tsx'),
    bundleType
  );
  chunkMessages(stats, bundle, createPath('messages.json'), bundleType);
  chunkThemes(
    stats,
    bundle,
    bundleType,
    path.resolve(rootDir, siteDir, bundleDir)
  );
  chunkThemeStyles(
    stats,
    bundle,
    path.resolve(rootDir, siteDir, bundleDir),
    bundleType
  );
  chunkThemeSnippetEditor(bundle, path.resolve(rootDir, siteDir, bundleDir));
  chunkBlockInfo(stats, bundle, createPath('blockInfo.tsx'), bundleType);
  chunkMessageJs(createPath('message.tsx'), bundleType);
  chunkRoutes(stats, bundle, createPath('routes.tsx'), bundleType);
  chunkAllViews(stats, bundle, createPath('views.tsx'), bundleType, undefined);
  chunkAllServices(stats, bundle, createPath('services.tsx'), bundleType);
  chunkAllMockedService(
    stats,
    bundle,
    createPath('mockServices.tsx'),
    bundleType
  );
  chunkProduce(stats, bundle, createPath('produce.tsx'), bundleType);
  chunkSettingFile(settings, createPath('settings.tsx'), bundleType);
  // process chunked language.
  // console.log(chalk.cyan(JSON.stringify(stats, null, '  ')));
}

module.exports = function buildBundle({ profile, type: bundleType }) {
  log.heading('UPDATE BUNDLE SETTING ' + process.env.MFOX_BUILD_TYPE);

  log.dump(paths);
  log.info('Proxy file:', paths.proxyJson);
  log.info('Env file:', paths.dotenv);
  log.info('Setting file:', paths.appSettingJson);

  if (!bundleType) {
    bundleType = process.env.MFOX_BUILD_TYPE ?? 'web';
  }

  const start = new Date().getTime();
  bundleFor({
    siteName: '@metafox/react-app',
    bundleDir: `src/bundle-${bundleType}`,
    bundleType
  });
  const spend = (new Date().getTime() - start) / 1000;
  log.info('Updated build settings in', spend, 'seconds');
};
