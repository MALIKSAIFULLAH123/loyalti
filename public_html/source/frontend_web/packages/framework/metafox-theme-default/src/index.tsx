/**
 * @type: theme
 * name: a0
 * system: true
 * bundle: web
 */
import styles from './styles.json';
import processors from './processors';

const config = {
  originBlockLayouts: require('./layout.blocks.origin.json'),
  originGridLayouts: require('./layout.grids.origin.json'),
  originItemLayouts: require('./layout.items.origin.json'),
  originNoContentLayouts: require('./layout.noContents.origin.json'),
  originTemplates: require('./layout.templates.origin.json'),
  originPageLayouts: require('./layout.pages.origin.json'),
  originIntergrationLayouts: require('./layout.intergration.origin.json'),
  originSiteBlocks: require('./layout.siteBlocks.origin.json'),
  originSiteDocks: require('./layout.siteDocks.origin.json'),
  originSiteFixedDocks: require('./layout.siteFixedDocks.origin.json'),
  blockLayouts: require('./layout.blocks.json'),
  gridLayouts: require('./layout.grids.json'),
  itemLayouts: require('./layout.items.json'),
  noContentLayouts: require('./layout.noContents.json'),
  templates: require('./layout.templates.json'),
  pageLayouts: require('./layout.pages.json'),
  siteBlocks: require('./layout.siteBlocks.json'),
  pageSizesPriority: {
    medium: ['large']
  },
  processors,
  styles
};

export default config;
