/**
 * @type: service
 * name: layoutBackend
 */
import {
  ConfiguredLayout,
  IS_ADMINCP,
  IS_WEB,
  RenderBaseItem,
  WithNoContentProps
} from '@metafox/framework';
import { Manager } from '@metafox/framework/Manager';
import { ThemeVariant } from '@metafox/layout';
import { randomId, requireParam } from '@metafox/utils';
import {
  difference,
  get,
  sortBy,
  intersection,
  isArray,
  concat,
  merge,
  range,
  set,
  has,
  unset,
  isPlainObject,
  isEqualWith,
  mergeWith,
  uniq,
  isString
} from 'lodash';
import { arrayToTree } from 'performant-array-to-tree';
import { MAIN_SLOT_NAME } from './constants';
import deviceList from './DeviceList';
import KeyIndexer from './KeyIndexer';
import {
  BlockFeatureCreator,
  BlockOrigin,
  BlockUpdater,
  BlockViewConfig,
  BlockViewDictionary,
  ContentParams,
  EditBlockParams,
  EditContainerParams,
  EditMode,
  EditSlotParams,
  GridLayoutShape,
  ItemLayoutShape,
  JsxShape,
  LayoutPageStateShape,
  LayoutShape,
  LayoutUpdater,
  LoadedTheme,
  LoadedVariant,
  MasterPageConfig,
  PageLayoutShape,
  PageSize,
  SectionConfig,
  TemplateConfig,
  TemplateData,
  ThemeInfo,
  UpdatePageProps,
  VariantInfo
} from './types';
import { getElementPath, parseElementPath, toSlotArray } from './utils';

const LAYOUT_PAGES = 'layoutPages';
const LAYOUT_TEMPLATES = 'layoutTemplates';

function equalUrls(url1, url2) {
  return (
    url1 &&
    url2 &&
    new URL(url1, 'http://locahost').pathname ===
      new URL(url2, 'http://locahost').pathname
  );
}

export default class LayoutBackend {
  /**
   * mark as fix object clone.
   */
  private _fixObjectClone = false;

  private fallbackSize: PageSize = 'medium';
  /**
   * List of key sizes
   */
  private allSizes: PageSize[] = ['small', 'sMedium', 'medium', 'large'];
  /**
   * + Manager
   */
  public static readonly configKey: string = 'layouts';

  private manager: Manager;

  // current page layouts
  private pageLayouts: LayoutShape = {};

  // origin page layout, keep to compare later.
  private originPageLayouts?: LayoutShape;

  private originIntergrationLayouts?: LayoutShape;

  private pageSizesPriority?: Array<string>;

  private templateNameDefaults?: Record<string, any>;

  private editingTemplateName: string;

  /**
   * check editor is update changed but there is dirty.
   */
  private themeDirty: boolean = false;

  private variantDirty: boolean = false;

  private templates: TemplateData;

  private originTemplates?: TemplateData;

  private originSiteBlocks?: unknown;

  private siteBlocks: Record<PageSize, any> = {};

  /**
   * original theme configuration, packaged by theme.
   */
  private originVariant: ThemeVariant;

  /**
   * current theme variant
   */
  private themeInfo: ThemeInfo;

  private variantInfo: VariantInfo;

  /**
   * custom theme variant
   */
  private variant: ThemeVariant;

  private gridPresets: Record<string, GridLayoutShape>;

  private originGridPresets?: Record<string, GridLayoutShape>;

  private itemPresets: Record<string, ItemLayoutShape>;

  private originItemPresets: Record<string, ItemLayoutShape>;

  private blockPresets: Record<string, LayoutShape>;

  private originBlockPresets: Record<string, LayoutShape>;

  private noContentPresets: Record<string, WithNoContentProps>;

  private originNoContentPresets: Record<string, WithNoContentProps>;

  private blockView: BlockViewDictionary;

  private editingPageSize: PageSize;

  private siteDocks: string[] = [];

  private siteFixedDocks: string[] = [];

  private cachedData: Record<string, LayoutPageStateShape> = {};

  private layoutEditMode: EditMode = EditMode.launch;

  private asGuestMode: boolean = false;

  private ready: boolean = false;

  private blockFeatures: Record<string, BlockFeatureCreator>;

  private pageAltMap: Record<string, string> = {};

  /**
   * trigger to button cross context without re-render layout.
   */
  private publishEnabled: boolean = false;

  /**
   * trigger to button cross context without re-render layout.
   */
  private saveEnabled: boolean = false;

  private currentBlocks: JsxShape[] = [];

  public getBlockFeatures() {
    return this.blockFeatures;
  }

  /**
   * Get all site dock service annotation [@type:siteDock]
   *
   * @returns Render Array
   */
  public getSiteDockComponents(): {
    component: string;
    props: Record<string, any>;
  }[] {
    return this.siteDocks.map(name => ({
      component: name,
      props: { key: name }
    }));
  }

  public getSiteFixedDockComponents(): {
    component: string;
    props: Record<string, any>;
  }[] {
    return this.siteFixedDocks.map(name => ({
      component: name,
      props: { key: name }
    }));
  }

  public getAllBlockView(): BlockViewDictionary {
    return this.blockView;
  }

  public getBlockView(blockName: string): BlockViewConfig {
    return this.blockView[blockName];
  }

  public setBlockViews(info: {
    blocks: BlockViewDictionary;
    blockFeatures: Record<string, BlockFeatureCreator>;
  }) {
    this.blockView = info.blocks;
    this.blockFeatures = info.blockFeatures;
  }

  public bootstrap(manager: Manager) {
    this.manager = manager;
    const { constants, localStore } = manager;

    const isLayoutPreviewWindow = constants?.isLayoutPreviewWindow;
    const topWindow = window?.top as any;
    const selfWindow = window as any;

    selfWindow.layoutBackend = this;

    if (isLayoutPreviewWindow && topWindow.layoutBackend) {
      return (selfWindow.layoutBackend = topWindow.layoutBackend);
    }

    this.publishEnabled = !!localStore.get('layout.canPublish');
    this.saveEnabled = !!localStore.get('layout.canSave');

    this.loadFromLocal();

    return (selfWindow.layoutBackend = this);
  }

  private mergeLayoutConfig(target: TemplateConfig, template: TemplateConfig) {
    ['header', 'footer', 'content'].forEach(section => {
      if (template[section]) {
        Object.keys(template[section]).forEach(name => {
          if ('elements' === name) {
            target[section][name] = Object.assign(
              {},
              target[section][name],
              template[section][name]
            );
          } else {
            target[section][name] = template[section][name];
          }
        });
      }
    });

    if (template.blocks && template.blocks.length) {
      target.blocks = [...target.blocks, ...template.blocks];
    }

    if (template.overrides) {
      target.overrides = template.overrides;
    }
  }

  public async change(props: UpdatePageProps): Promise<LayoutPageStateShape> {
    if (this.ready) {
      return this.resolve(props).catch(null);
    }

    return this.waitToReady()
      .then(() => this.resolve(props))
      .catch(null);
  }

  public reload = () => {
    this.manager.eventCenter.dispatch('onLayoutRefresh', randomId());
  };

  /**
   * This method return cascading master config from parent to child
   *
   * @param templateName master page name
   * @param pageSize particular page size
   */
  private normalizeLayoutContent(
    templateName: string,
    pageSize: PageSize
  ): TemplateConfig {
    const skipLoop: Record<string, boolean> = {};
    const templates: TemplateConfig[] = [];
    const config: TemplateConfig = {
      templateName,
      blocks: [],
      // section header
      header: { elements: {} },
      // section content
      content: { elements: {} },
      // section footer
      footer: { elements: {} },
      //  disabled blocks
      disabledBlocks: []
    };

    // template inheritance is unnecessary.

    // avoid check extended.
    while (templateName && !skipLoop[templateName]) {
      const overrideConfig = this.getTemplateConfig(templateName);

      if (overrideConfig) {
        templates.unshift(overrideConfig);
        skipLoop[templateName] = true;
        // assign to check next page
        templateName = overrideConfig.templateName;
      } else {
        templateName = null;
      }
    }

    const siteBlocks = this.siteBlocks[pageSize];

    if (siteBlocks?.length) {
      config.blocks = [...siteBlocks];
    }

    templates.forEach(template => {
      this.mergeLayoutConfig(config, template);
    });

    // should do this block layout ?
    if (config.blocks) {
      config.blocks.forEach(block => {
        if (!block.blockOrigin) block.blockOrigin = 'layout';
      });
    }

    return config;
  }

  private getLookupSizes(pageSize: PageSize): PageSize[] {
    let foundIndex = this.allSizes.indexOf(pageSize);

    if (0 > foundIndex) {
      foundIndex = this.allSizes.indexOf(this.fallbackSize);
    }

    return uniq(
      [pageSize]
        .concat(
          get(this.pageSizesPriority, pageSize) || [],
          this.allSizes.slice(0, foundIndex).reverse(),
          this.allSizes.slice(foundIndex + 1)
        )
        .filter(x => !!x)
    );
  }

  private getFullKey(pageName: string, pageSize: PageSize): string {
    return `${pageSize}:${pageName}`;
  }

  private deepClone<T = any>(data: any): T {
    return JSON.parse(JSON.stringify(data));
  }

  /**
   * @param name
   * @param fallback
   */
  public getTemplateConfig(name: string): TemplateConfig | undefined {
    return this.templates[name];
  }

  /**
   * string
   * @param pageName
   * @param expectedSize
   */
  public getApproximatePageSize(pageName, expectedSize: PageSize): PageSize {
    if (!this.pageLayouts[pageName]) return undefined;

    return this.getLookupSizes(expectedSize).find(
      size => this.pageLayouts[pageName][size]
    );
  }

  /**
   * string
   * @param pageName
   * Screen size current
   * @param expectedSize
   */
  public getApproximatePageTemplateName(
    pageName,
    expectedSize: PageSize
  ): string {
    const approximatePageSize = this.getApproximatePageSize(
      pageName,
      expectedSize
    );

    if (!this.pageLayouts[pageName]) return null;

    const templateName =
      this.pageLayouts[pageName][approximatePageSize].templateName;

    if (
      expectedSize !== approximatePageSize &&
      get(this.templateNameDefaults, expectedSize)
    ) {
      const templateFallback = get(this.templateNameDefaults, expectedSize);

      if (isString(templateFallback)) {
        return templateFallback;
      } else {
        return (
          get(templateFallback, templateName) ||
          get(templateFallback, '_default')
        );
      }
    }

    return templateName;
  }

  /**
   * get particular layout configuration by "pageName" and "pageSize"
   * @param pageName
   * @param pageSize
   * @param fallback
   */
  public getApproximatePage(
    pageName: string,
    pageSize: PageSize
  ): PageLayoutShape | undefined {
    if (!this.pageLayouts[pageName]) return undefined;

    const size = this.getLookupSizes(pageSize).find(
      size => this.pageLayouts[pageName][size]
    );

    if (size) {
      return this.pageLayouts[pageName][size];
    }
  }

  public getProperPage(
    pageName: string,
    pageSize: PageSize,
    autoCreate: boolean = false
  ): PageLayoutShape {
    if (
      autoCreate &&
      !this.pageLayouts[pageName] &&
      this.pageAltMap[pageName]
    ) {
      const pageNameAlt = this.pageAltMap[pageName];

      // how to map back to page layouts.
      this.pageLayouts[pageName] = this.deepClone(
        this.pageLayouts[pageNameAlt]
      );
      // keep this info when for scripts/saveLayout() method work correctly.
      this.pageLayouts[pageName].pageNameAlt = pageNameAlt;
    }

    if (this.pageLayouts[pageName] && this.pageLayouts[pageName][pageSize]) {
      return this.pageLayouts[pageName][pageSize];
    }

    const origin = this.getApproximatePage(pageName, pageSize);

    if (!origin && !autoCreate) {
      throw new Error(`Invalid pageName ${pageName}`);
    }

    this.pageLayouts[pageName][pageSize] = this.deepClone<PageLayoutShape>(
      origin ? origin : { blocks: [] }
    );

    return this.pageLayouts[pageName][pageSize];
  }

  /**
   * Check page exists
   *
   * @param pageName
   * @returns boolean
   */
  public hasPage(pageName: string): boolean {
    return Boolean(this.pageLayouts[pageName]);
  }

  public getPageLayout(pageName: string, pageSize: PageSize): PageLayoutShape {
    const pageLayout: PageLayoutShape = this.getApproximatePage(
      pageName,
      pageSize
    );

    if (!pageLayout) {
      return {
        blocks: [],
        overrides: {}
      };
    }

    if (pageLayout.blocks) {
      pageLayout.blocks.forEach(item => {
        if (!item.blockId) {
          item.blockId = randomId();
        }

        item.blockOrigin = 'page';
      });
    }

    const derivedLayout: LayoutPageStateShape = JSON.parse(
      JSON.stringify(pageLayout)
    );

    return derivedLayout;
  }

  public setAltMap(pageName: string, pageNameAlt: string): void {
    this.pageAltMap[pageName] = pageNameAlt;
  }

  public resolve({
    pageName,
    pageNameAlt,
    pageNameAsGuest,
    pageSize: inputSize,
    pageParams,
    pageHelmet,
    previewDevice,
    contentParams,
    update,
    masterPage,
    loginRequired,
    isLogged,
    refreshToken
  }: UpdatePageProps): Promise<LayoutPageStateShape> {
    // await until templates is loaded
    const hasAdminAccess = this.manager.getAcl('core.admincp.has_admin_access');
    const offline = this.manager.getSetting('core.offline');
    const offlineRedirect = this.manager.getSetting(
      'core.offline_static_page_url'
    );
    // acl.user.
    // wait until template is ready

    let name = pageName;
    const asGuest = this.asGuestMode;

    if ((!isLogged || asGuest) && loginRequired) {
      if (refreshToken && !asGuest) {
        name = 'core.refreshToken';
      } else {
        name = pageNameAsGuest || 'user.login';
      }
    }

    if (!hasAdminAccess && IS_ADMINCP) {
      name = 'admincp.user.login';
    }

    if (!this.hasPage(name) && this.hasPage(pageNameAlt)) {
      name = pageNameAlt;
      this.setAltMap(pageName, pageNameAlt);
    }

    if (!this.hasPage(name)) {
      name = 'core.error404';
    }

    if (!hasAdminAccess && offline && IS_WEB) {
      if (offlineRedirect) {
        if (!equalUrls(window?.location?.href, offlineRedirect)) {
          this.manager?.redirectTo(offlineRedirect);
        }
      } else {
        name = 'core.offline';
      }
    }

    const layoutEditMode: EditMode = previewDevice
      ? EditMode.launch
      : this.layoutEditMode;
    const pageSizeCurrent =
      this.editingPageSize && !previewDevice ? this.editingPageSize : inputSize;
    const pageSize = this.getApproximatePageSize(name, pageSizeCurrent);
    const pageLayout = this.getPageLayout(name, pageSize);
    const templateName =
      this.editingTemplateName ??
      this.getApproximatePageTemplateName(name, pageSizeCurrent) ??
      'default';

    const derived: LayoutPageStateShape = this.normalizePageContent(
      name,
      templateName,
      pageSize,
      layoutEditMode,
      masterPage
    );

    let blocks;

    if (layoutEditMode === EditMode.editSiteContent) {
      blocks = derived.blocks;
    } else if (layoutEditMode === EditMode.editLayout) {
      blocks = [];
    } else {
      blocks = [].concat(pageLayout.blocks, derived.blocks);
    }

    blocks = blocks
      .filter(block => block && block.slotName)
      .map((block, index) => ({
        ...block,
        blockOrder: block?.blockOrder || index
      }));

    const { overrides } = pageLayout;

    if (overrides) {
      blocks = blocks.map(block => ({
        ...block,
        ...overrides[block.blockId]
      }));
    }

    // sort block
    blocks = sortBy(blocks, 'blockOrder');

    // Fix Warning: Each child in a list should have a unique "key" prop.
    // For better render performance block.key should be kept cross site if the same block.
    //
    const indexer = new KeyIndexer();

    blocks.forEach(block => {
      if (!block.key) {
        block.key = indexer.forBlock(block.slotName, block.component);
      }
    });

    this.currentBlocks = blocks;

    const tree = arrayToTree(blocks, {
      parentId: 'parentBlockId',
      childrenField: 'elements',
      id: 'blockId',
      dataField: null
    });

    const rootBlocks = tree
      .map((block, index) =>
        this.transformBlock(
          block,
          name,
          pageSize,
          layoutEditMode,
          contentParams,
          index
        )
      )
      .filter(Boolean) as any;

    derived.previewDevice = previewDevice;
    derived.contentParams = contentParams;
    derived.pageName = pageNameAlt ?? name;
    derived.pageSize = pageSize;
    derived.templateName = templateName;
    derived.layoutEditMode = layoutEditMode;
    derived.pageParams = pageParams;
    derived.pageHelmet = pageHelmet;
    derived.asMasterPage = !!masterPage;

    const result = {
      ...derived,
      pageName: name,
      blocks: rootBlocks
    };

    // pending until blockLayout is ready
    update(result);

    return Promise.resolve(result);
  }

  public waitToReady(): Promise<boolean> {
    return new Promise(resolve => {
      const checker = () => {
        if (this.ready) {
          resolve(true);
        } else {
          setTimeout(checker, 0);
        }
      };
      checker();
    });
  }

  public normalizePageContent(
    pageName: string,
    templateName: string,
    pageSize: PageSize,
    layoutEditMode: EditMode,
    masterPage: MasterPageConfig
  ): LayoutPageStateShape {
    const cacheId = `${templateName}.${pageSize}.${layoutEditMode}.${
      masterPage ? 1 : 0
    }`;

    if (this.cachedData[cacheId]) {
      return this.cachedData[cacheId];
    }

    const templateConfig = this.normalizeLayoutContent(templateName, pageSize);

    this.transformSection(
      templateConfig.header,
      'header',
      templateName,
      layoutEditMode,
      masterPage
    );

    this.transformSection(
      templateConfig.content,
      'content',
      templateName,
      layoutEditMode,
      masterPage
    );

    this.transformSection(
      templateConfig.footer,
      'footer',
      templateName,
      layoutEditMode,
      masterPage
    );

    const result = Object.assign(
      {
        blocks: [],
        overrides: {},
        templateName,
        pageName,
        pageSize
      },
      templateConfig
    );

    this.cachedData[cacheId] = result;

    return result;
  }

  private transformSection = (
    section: SectionConfig,
    sectionName: string,
    templateName: string,
    layoutEditMode: EditMode,
    masterPage: MasterPageConfig
  ): void => {
    section.elements = Object.keys(section.elements)
      .map((containerName: any, index: number) =>
        this.transformContainer(
          section.elements[containerName],
          containerName,
          sectionName,
          templateName,
          layoutEditMode,
          masterPage
        )
      )
      .filter(Boolean);
  };

  private transformContainer(
    { component, ...props },
    containerName: string,
    sectionName: string,
    templateName: string,
    layoutEditMode: EditMode,
    masterPage: MasterPageConfig
  ): JsxShape {
    const elementPath = getElementPath(sectionName, containerName);
    props.containerName = containerName;
    props.sectionName = sectionName;
    props.layoutEditMode = layoutEditMode;
    props.elementPath = elementPath;
    props.templateName = templateName;
    props.key = `container.${containerName}`;
    props.gridContainer = props.elements
      ? 1 < Object.keys(props.elements).length
      : false;

    if (layoutEditMode === EditMode.editSiteContent) {
      component = 'layout.containerWithEditingPage';
    } else if (layoutEditMode === EditMode.editPageContent) {
      component = 'layout.containerWithEditingPage';
    } else if (layoutEditMode === EditMode.editLayout) {
      component = 'layout.containerWithEditingTemplate';
    } else if (!component) {
      component = 'layout.containerWithLiveView';
    }

    props.elements = toSlotArray(props.elements)
      .map(child =>
        this.transformSlot(
          child,
          elementPath,
          templateName,
          layoutEditMode,
          masterPage
        )
      )
      .filter(Boolean);

    return { component, props };
  }

  private transformSlot(
    { component, ...props },
    parentPath: string,
    templateName: string,
    layoutEditMode: EditMode,
    masterPage: MasterPageConfig
  ) {
    props.key = `slot.${props.slotName}`;

    if (layoutEditMode === EditMode.editSiteContent) {
      component = 'layout.slot.EditPageContent';
    } else if (layoutEditMode === EditMode.editPageContent) {
      component = 'layout.slot.EditPageContent';
    } else if (layoutEditMode === EditMode.editLayout) {
      component = 'layout.slot.EditLayout';
    } else if (!component) {
      component = 'layout.slot.LiveEdit';
    }

    const elementPath = getElementPath(parentPath, props.slotName);

    props.elementPath = elementPath;
    props.layoutEditMode = layoutEditMode;
    props.templateName = templateName;

    if (MAIN_SLOT_NAME === props.slotName && masterPage) {
      // not to be block but child component.
      props.elements = [
        { component: 'layout.childPageContainer', props: { masterPage } }
      ];
    } else if (props.elements) {
      props.elements = toSlotArray(props.elements)
        .map((child, index) =>
          this.transformSlot(
            child,
            elementPath,
            templateName,
            layoutEditMode,
            undefined
          )
        )
        .filter(Boolean);
    }

    return { component, props };
  }

  private transformBlock = (
    {
      component,
      elements: tempElements,
      blockDisabled,
      blockOrigin,
      ...props
    }: any,
    pageName: string,
    pageSize: PageSize,
    layoutEditMode: EditMode,
    mainContent: ContentParams,
    index: number
  ): void | RenderBaseItem => {
    if (!component) return;

    if (!props.slotName) return;

    if (!props.blockId) props.blockId = randomId();

    const key = props.key;

    // props.key = index.toString();

    const elements = isArray(tempElements)
      ? (tempElements
          .map(element =>
            this.transformBlock(
              element,
              pageName,
              pageSize,
              layoutEditMode,
              mainContent,
              index
            )
          )
          .filter(Boolean) as any)
      : undefined;

    props.elements = elements;

    if (layoutEditMode === EditMode.editPageContent) {
      return {
        component: 'layout.blockWithEditingPage',
        slotName: props.slotName,
        blockId: props.blockId,
        props: {
          key,
          freeze: props.freeze,
          blockId: props.blockId,
          title: props.title,
          parentBlockId: props.parentBlockId,
          blockName: component,
          blockOrigin,
          blockDisabled,
          slotName: props.slotName,
          elements,
          layoutEditMode,
          pageName,
          pageSize,
          extra: mainContent
        }
      };
    } else if (layoutEditMode === EditMode.editLayout) {
      return {
        component: 'layout.blockWithEditingPage',
        slotName: props.slotName,
        blockId: props.blockId,
        props: {
          key,
          title: props.title,
          freeze: props.freeze,
          blockId: props.blockId,
          parentBlockId: props.parentBlockId,
          slotName: props.slotName,
          blockName: component,
          blockOrigin,
          blockDisabled,
          layoutEditMode,
          elements,
          pageName,
          pageSize,
          extra: mainContent
        }
      };
    } else if (layoutEditMode === EditMode.editSiteContent) {
      return {
        component: 'layout.blockWithEditingSite',
        slotName: props.slotName,
        blockId: props.blockId,
        props: {
          key,
          title: props.title,
          freeze: props.freeze,
          blockId: props.blockId,
          parentBlockId: props.parentBlockId,
          slotName: props.slotName,
          blockName: component,
          blockOrigin,
          blockDisabled,
          layoutEditMode,
          elements,
          pageName,
          pageSize,
          extra: mainContent
        }
      };
    } else if (layoutEditMode === EditMode.editLive) {
      if (blockDisabled) {
        return;
      }

      return {
        component: 'layout.Fragment',
        slotName: props.slotName,
        blockId: props.blockId,
        props: {
          blockId: props.blockId,
          slotName: props.slotName,
          pageSize,
          layoutEditMode,
          blockOrigin,
          blockDisabled,
          pageName,
          key,
          elements: [
            {
              component: 'layout.blockWithQuickEdit',
              slotName: props.slotName,
              blockId: props.blockId,
              props: {
                blockId: props.blockId,
                parentBlockId: props.parentBlockId,
                blockName: component,
                freeze: props.freeze,
                slotName: props.slotName,
                pageSize,
                layoutEditMode,
                elements,
                pageName,
                extra: mainContent,
                key: `edit.${key}`
              }
            },
            {
              component,
              slotName: props.slotName,
              blockId: props.blockId,
              props
            }
          ]
        }
      };
    }

    if (blockDisabled) {
      return null;
    }

    return {
      component,
      slotName: props.slotName,
      blockId: props.blockId,
      props
    };
  };

  public discardChanges(): void {
    const store = this.manager?.localStore;

    if (!store) return;

    // eslint-disable-next-line
    window.location.href = window.location.href;
  }

  public saveLocal() {
    const { localStore: store, preferenceBackend } = this.manager;
    const themeId = preferenceBackend.getTheme();

    if (!store) return;

    store.set(`${LAYOUT_PAGES}-${themeId}`, JSON.stringify(this.pageLayouts));
    store.set(`${LAYOUT_TEMPLATES}-${themeId}`, JSON.stringify(this.templates));
  }

  public loadFromLocal() {
    const { localStore: store, preferenceBackend } = this.manager;

    const themeId = preferenceBackend.getTheme();

    if (!store) return;

    const pages = store.getJSON<LayoutShape>(`${LAYOUT_PAGES}-${themeId}`);
    const templates = store.getJSON<TemplateData>(
      `${LAYOUT_TEMPLATES}-${themeId}`
    );

    if (pages) {
      this.pageLayouts = pages;
    }

    if (templates) {
      this.templates = templates;
    }
  }

  public updateSiteBlock(pageSize: PageSize, updater: LayoutUpdater) {
    // @todo fix blog page size ?
    this.siteBlocks[pageSize] = updater(this.siteBlocks[pageSize]);
    this.setThemeDirty(true);
    // this.saveLocal();
    this.reload();
  }

  public updatePageBlock(
    pageName: string,
    pageSize: PageSize,
    updater: LayoutUpdater
  ) {
    // @todo fix blog page size ?
    this.pageLayouts[pageName][pageSize].blocks = updater(
      this.pageLayouts[pageName][pageSize].blocks
    );

    this.setThemeDirty(true);
    // this.saveLocal();
    this.reload();
  }

  public getLayoutConfig(
    pageName: string,
    pageSize: PageSize
  ): PageLayoutShape {
    // @todo fix page size
    return this.getProperPage(pageName, pageSize, true);
  }

  public getBlockConfig(
    pageName: string,
    pageSize: PageSize,
    blockId: string,
    blockOrigin: BlockOrigin
  ): JsxShape | undefined {
    if (blockOrigin === 'site') {
      const blocks = this.siteBlocks[pageSize];

      if (blocks?.length) {
        return blocks.find(x => x.blockId === blockId);
      }

      return undefined;
    }

    const layout = this.getLayoutConfig(pageName, pageSize);

    return layout.blocks.find(x => x.blockId === blockId);
  }

  public cleanStyleByPreset(obj: any) {
    const { blockLayout, gridLayout } = obj;

    if (blockLayout !== 'none') {
      const styleProps = this.getBlockPreset(blockLayout);

      if (styleProps) {
        Object.keys(styleProps).forEach(key => delete obj[key]);
      }
    }

    if (gridLayout !== 'none') {
      const styleProps = this.getGridPreset(gridLayout);

      if (styleProps) {
        Object.keys(styleProps).forEach(key => delete obj[key]);
      }
    }

    if (!obj.blockId) {
      obj.blockId = randomId();
    }

    delete obj.blockProps;
    delete obj.key;
  }

  public updateBlockConfig(
    pageName: string,
    pageSize: PageSize,
    blockId: string,
    blockOrigin: BlockOrigin,
    updater: BlockUpdater
  ) {
    // ensure page is created
    this.getProperPage(pageName, pageSize, true);
    const prevBlock = this.getBlockConfig(
      pageName,
      pageSize,
      blockId,
      blockOrigin
    );

    if (!prevBlock) {
      // console.warn('Could not find block');

      return;
    }

    const nextBlock = updater(prevBlock);

    this.cleanStyleByPreset(nextBlock);

    const calBlocks = prev => {
      if (!prev) {
        prev = [];
      }

      const index = prev.findIndex(x => nextBlock.blockId === x.blockId);

      if (-1 < index) {
        prev[index] = nextBlock;
      } else {
        prev.unshift(nextBlock);
      }

      return prev;
    };

    if (blockOrigin === 'site') {
      // update SiteBlock
      this.updateSiteBlock(pageSize, prev => calBlocks(prev));
    } else {
      this.updatePageBlock(pageName, pageSize, prev => calBlocks(prev));
    }
  }

  public getPageConfig(pageName: string) {
    return this.pageLayouts[pageName];
  }

  public getPageLayouts(): LayoutShape {
    return this.pageLayouts;
  }

  public getTemplates(): TemplateData {
    return this.templates;
  }

  public fixObjectClone() {
    if (this._fixObjectClone) return;

    this._fixObjectClone = true;

    // keep a copy of current page layout to compare later
    this.originPageLayouts = this.cloneJson(this.originPageLayouts);
    this.originIntergrationLayouts = this.cloneJson(
      this.originIntergrationLayouts
    );
    this.originBlockPresets = this.cloneJson(this.originBlockPresets);
    this.originItemPresets = this.cloneJson(this.originItemPresets);
    this.originGridPresets = this.cloneJson(this.originGridPresets);
    this.originNoContentPresets = this.cloneJson(this.originNoContentPresets);
    this.originBlockPresets = this.cloneJson(this.originBlockPresets);
    this.originNoContentPresets = this.cloneJson(this.originNoContentPresets);
    this.originSiteBlocks = this.cloneJson(this.originSiteBlocks);
    this.originTemplates = this.cloneJson(this.originTemplates);
  }

  get customTemplates() {
    return this.getDiff(this.templates, this.originTemplates);
  }

  get customGridPresets() {
    return this.getDiff(this.gridPresets, this.originGridPresets);
  }

  get customItemPresets() {
    return this.getDiff(this.itemPresets, this.originItemPresets);
  }

  get customBlockPresets() {
    return this.getDiff(this.blockPresets, this.originBlockPresets);
  }

  get customNoContentPresets() {
    return this.getDiff(this.noContentPresets, this.originNoContentPresets);
  }

  get customPageLayouts(): LayoutShape {
    return this.getDiff(this.pageLayouts, this.originPageLayouts);
  }

  get customSiteBlocks() {
    return this.getDiff(this.siteBlocks, this.originSiteBlocks);
  }

  /**
   * @see @metafox/layout/constants
   *
   * @param mode
   */
  public setEditMode(mode: EditMode) {
    this.layoutEditMode = mode;

    switch (mode) {
      case EditMode.editLayout:
      case EditMode.editLive:
      case EditMode.editSiteContent:
      case EditMode.editPageContent:
        this.fixObjectClone();
        break;
      case EditMode.launch:
    }

    this.manager.preferenceBackend.setAndRemember('layoutEditMode', mode);

    this.clearCache();
    this.reload();
  }

  /**
   * @see @metafox/layout/constants
   *
   * @param asGuest
   */
  public setAsGuestMode(mode: boolean) {
    this.asGuestMode = mode;

    this.manager.preferenceBackend.setAndRemember('asGuestMode', mode);

    this.clearCache();
    this.reload();
  }

  public getAsGuestMode() {
    return this.asGuestMode;
  }

  /**
   *
   * @param name - Change to template name
   * @param props - Props required {pageName: string, pageSize: string}
   * @returns
   */
  public changeTemplate(name: string, props: any): void {
    if (!this.templates[name]) {
      throw new Error(`Unexpected template ${name}`);
    }

    // unset template name
    this.editingTemplateName = undefined;

    const { pageName, pageSize } = props;

    const page = this.getProperPage(pageName, pageSize, true);

    if (page) {
      page.templateName = name;
    }

    this.afterEdit();
  }

  public addBlock(props: any) {
    requireParam(props, 'blockName, slotName, pageName, pageSize');

    const { blockId, parentBlockId, blockName, slotName, pageName, pageSize } =
      props;

    const blockOrigin: BlockOrigin =
      this.layoutEditMode === EditMode.editSiteContent ? 'site' : undefined;

    const block = {
      blockId,
      parentBlockId,
      component: blockName,
      slotName: slotName ? slotName : 'main',
      pageName,
      blockOrigin
    };

    if (blockOrigin === 'site') {
      this.addSiteBlock(pageSize, block);
    } else {
      const layout = this.getProperPage(pageName, pageSize, true);
      layout.blocks.unshift(block);
    }

    this.afterEdit();

    return block;
  }

  public resetPage(props: any) {
    requireParam(props, 'pageName, pageSize');

    const { pageName, pageSize } = props;

    const layout = this.getProperPage(pageName, pageSize, true);
    const layoutOrigin = this.originPageLayouts[pageName][pageSize];
    layout.blocks = layoutOrigin.blocks;

    this.afterEdit();
  }

  public resetSite(props: any) {
    requireParam(props, 'pageSize');

    const { pageSize } = props;

    this.siteBlocks[pageSize] = [...(this.originSiteBlocks[pageSize] || [])];

    this.afterEdit();
  }

  public toggleBlock(props: Partial<EditBlockParams>): void {
    const { blockId, pageName, pageSize } = props;

    const page = this.getProperPage(pageName, pageSize);

    if (!page || !page.blocks) return null;

    if (!page.overrides) {
      page.overrides = {};
    }

    if (!page.overrides[blockId]) {
      page.overrides[blockId] = {};
    }

    if (has(page.overrides, `${blockId}.blockDisabled`)) {
      unset(page.overrides, `${blockId}.blockDisabled`);
    } else {
      set(page.overrides, `${blockId}.blockDisabled`, true);
    }

    this.afterEdit();
  }

  public addSiteBlock(pageSize: PageSize, block: any) {
    if (!this.siteBlocks[pageSize]) {
      this.siteBlocks[pageSize] = [];
    }

    this.siteBlocks[pageSize] = concat([block], this.siteBlocks[pageSize]);
  }

  public deleteSiteBlock(pageSize: PageSize, blockId: string) {
    if (!this.siteBlocks[pageSize]) return;

    this.siteBlocks[pageSize] = this.siteBlocks[pageSize].filter(
      x => x.blockId !== blockId
    );
  }

  public deleteBlock(props: Partial<EditBlockParams>) {
    const { blockId, pageName, pageSize, blockOrigin } = props;

    if (blockOrigin === 'site') {
      this.deleteSiteBlock(pageSize, blockId);
    } else {
      const page = this.getProperPage(pageName, pageSize, true);

      if (!page || !page.blocks) {
        throw new Error(`Invalid pageName "${pageName}"`);
      }

      page.blocks = page.blocks.filter(block => block.blockId !== blockId);
    }

    this.afterEdit();
  }

  private afterEdit(): void {
    this.clearCache();
    this.setThemeDirty(true);
    // this.saveLocal();
    this.reload();
  }

  public getContainerConfig(props: EditContainerParams): any {
    requireParam(props, 'templateName, elementPath');

    const template = this.getTemplateConfig(props.templateName);

    return get(template, props.elementPath);
  }

  public setContainerConfig(props, updater) {
    requireParam(props, 'templateName, elementPath');
    const template = this.getTemplateConfig(props.templateName);
    const values = this.getContainerConfig(props);
    set(template, props.elementPath, updater(values));

    this.afterEdit();
  }

  public getSectionConfig(props: EditContainerParams) {
    requireParam(props, 'templateName, containerName, elementPath');

    const template = this.getTemplateConfig(props.templateName);
    const [, parentPath] = parseElementPath(props.elementPath);
    const parent = get(template, parentPath);

    return parent;
  }

  public deleteContainer(props: EditContainerParams): void {
    requireParam(props, 'templateName, containerName, elementPath');

    const template = this.getTemplateConfig(props.templateName);
    const [name, parentPath] = parseElementPath(props.elementPath);
    const parent = get(template, parentPath);

    delete parent.elements[name];

    this.afterEdit();
  }

  public getSlotConfig(props: EditSlotParams) {
    requireParam(props, 'elementPath, templateName');

    const template = this.getTemplateConfig(props.templateName);

    return get(template, props.elementPath);
  }

  public setSlotConfig(
    props: EditSlotParams,
    updater: (data: any) => any
  ): void {
    const template = this.getTemplateConfig(props.templateName);
    const config = this.getSlotConfig(props);

    set(template, props.elementPath, updater(config));

    this.afterEdit();
  }

  public deleteSlot(props: EditSlotParams): void {
    requireParam(props, 'templateName, elementPath');
    const template = this.getTemplateConfig(props.templateName);
    const [name, parentPath] = parseElementPath(props.elementPath);
    const parent = get(template, parentPath);
    delete parent.elements[name];

    this.afterEdit();
  }

  addNewContainer(
    props: EditContainerParams,
    numberOfSlots: number,
    offset: number
  ) {
    requireParam(props, 'containerName, templateName');
    const currentContainer = this.getContainerConfig(props);
    const containerName = randomId();
    const [, parentPath] = parseElementPath(props.elementPath);
    const section = this.getSectionConfig(props);

    // generate new path
    // how to process this case.
    const elementPath = [parentPath, 'elements', containerName].join('.');

    const newContainer = this.deepClone({
      ...currentContainer,
      elementPath,
      elements: {}
    });
    const newProps = { ...props, elementPath, containerName };

    const names = Object.keys(section.elements);
    section.elements[containerName] = newContainer;

    const elements = names
      .filter((_, index) => index <= offset)
      .concat([containerName])
      .concat(names.filter((_, index) => index > offset))
      .reduce((acc, name) => {
        acc[name] = section.elements[name];

        return acc;
      }, {});

    // reorder container

    section.elements = elements;

    // add 4 slot to this container

    this.addNewSlots(newProps, numberOfSlots, 1);
  }

  public addNewSlots(
    props: EditContainerParams,
    numberOfSlots: number,
    offset: number
  ): void {
    requireParam(props, 'containerName, templateName');
    const container = this.getContainerConfig(props);

    const arr = range(0, numberOfSlots).map(index => randomId());

    const names = Object.keys(container.elements ?? {});

    const elements = names
      .filter((_, index) => index <= offset)
      .concat(arr)
      .concat(names.filter((_, index) => index > offset))
      .reduce((acc, name) => {
        acc[name] = container.elements[name] ?? { slotName: name, sx: 2 };

        return acc;
      }, {});

    // force change element
    container.elements = elements;

    this.afterEdit();
  }

  /**
   * @private
   *
   * Clear cached result
   * @return void
   */
  private clearCache(): void {
    this.cachedData = {};
  }

  public getPageSizeByDeviceName(value: string): PageSize {
    const found = deviceList.find(x => x.value === value);

    if (found) {
      const width = parseInt(found.size.replace(/\x.*$/, ''));

      return this.getPageSizeByWidth(width);
    }

    return 'small';
  }

  public getDeviceName(value: string): string {
    const found = deviceList.find(x => x.value === value);

    if (found) {
      return found.label;
    }

    return 'Unknown';
  }

  public getPageSizeByWidth(width?: number): PageSize {
    const w = width || window.innerWidth || 600;

    if (767 >= w) {
      return 'small';
    }

    if (1023 >= w) {
      return 'sMedium';
    }

    if (1280 >= w) {
      return 'medium';
    }

    return 'large';
  }

  /**
   * Get avaiable page size of particular pageName string params
   * @param pageName  string
   * @return string[]
   */
  public getExistingSizes(pageName: string): PageSize[] {
    const page = this.pageLayouts[pageName];

    if (!page) return [];

    return intersection(
      Object.keys(page).map(x => x as PageSize),
      this.allSizes
    );
  }

  public addPage(props: {
    pageName: string;
    pageSize: PageSize;
    baseName: string;
    baseSize: PageSize;
  }) {
    requireParam(props, 'pageName, pageSize');

    const origin = this.getApproximatePage(props.baseName, props.baseSize);
    const key = this.getFullKey(props.pageName, props.pageSize);

    // todo check key exists to throw error

    if (this.pageLayouts[key]) {
      throw new Error(`Duplicated ${props.pageName} with ${props.pageSize}`);
    }

    const page = this.deepClone(origin);

    this.pageLayouts[key] = page;

    this.afterEdit();
  }

  public getPageSizeOptions(): { value: PageSize; label: string }[] {
    return this.allSizes.map(x => ({ value: x, label: x }));
  }

  public getAllSizes(): PageSize[] {
    return this.allSizes;
  }

  /**
   * Following sizes can not be remove when manage layout.
   * Frontend engineer must add all of there page size configuration.
   * @return string
   */
  public getMainSizes(): PageSize[] {
    return ['small', 'medium', 'large'];
  }

  public setEnabledSizes(pageName: string, sizes: PageSize[]): void {
    if (!sizes.length || !this.pageLayouts[pageName]) {
      return;
    }

    sizes.forEach(pageSize => {
      this.getProperPage(pageName, pageSize, true);
    });

    difference(Object.keys(this.pageLayouts[pageName]), sizes).forEach(
      pageSize => {
        delete this.pageLayouts[pageName][pageSize];
      }
    );
  }

  public getTemplateConfigBySize(pageName: string): Record<PageSize, string> {
    const page = this.pageLayouts[pageName];
    const result = {};

    Object.keys(page).forEach(size => {
      result[size] = page[size].templateName ?? 'default';
    });

    return result as any;
  }

  public setTemplateConfigBySize(props: {
    pageName: string;
    config: Record<PageSize, string>;
  }): void {
    requireParam(props, 'pageName, config');
    const { pageName, config } = props;

    (Object.keys(props.config) as PageSize[]).forEach((pageSize: PageSize) => {
      const page = this.getProperPage(pageName, pageSize, true);

      if (page) {
        page.templateName = config[pageSize];
      }
    });
    this.reload();
  }

  public leaveEditPageSize(): void {
    this.editingPageSize = undefined;
    this.reload();
  }

  public enterEditPageSize(pageName: string, pageSize: PageSize): void {
    this.getProperPage(pageName, pageSize);
    this.editingPageSize = pageSize;
    this.reload();
  }

  /**
   *
   * @param templateName - Template name
   */
  public editTemplate(templateName: string): void {
    this.editingTemplateName = templateName;

    this.setEditMode(EditMode.editLayout);

    this.afterEdit();
  }

  /**
   *
   * @param base - Inherit template name
   * @param name - new template key
   */
  public addNewTemplate(base: string, name: string): void {
    if (!this.templates[base]) {
      throw new Error('Unexpected base layout');
    }

    if (this.templates[name]) {
      throw new Error(`Layout "${name}" is already exists.`);
    }

    // clone template from base template
    this.templates[name] = this.deepClone(this.templates[base]);

    this.clearCache();
  }

  public updateBlockPosition(props: {
    blockId: string;
    order: number;
    slotName: string;
    templateName: string;
    pageSize: PageSize;
    pageName: string;
  }) {
    const { blockId, order: orderNew, slotName, pageName, pageSize } = props;
    const blocks = this.currentBlocks;

    // ordering require side and any blocks.
    const page = this.getProperPage(pageName, pageSize);

    if (!page) return null;

    const editBlock = blocks.find(block => block.blockId === blockId);

    if (!editBlock) return;

    // update block SlotName
    if (editBlock.slotName !== slotName) {
      editBlock.slotName = slotName;

      if (editBlock.blockOrigin === 'site') {
        const siteBlocks = this.siteBlocks[pageSize] ?? [];
        const siteBlock = siteBlocks.find(block => block.blockId === blockId);

        if (siteBlock) {
          siteBlock.slotName = slotName;
        }
      } else {
        const pageBlock = page.blocks.find(block => block.blockId === blockId);

        if (pageBlock) {
          pageBlock.slotName = slotName;
        }
      }
    }

    let { overrides } = page;

    if (!overrides) {
      overrides = {};
    }

    try {
      let blockOnSlot = blocks.filter(
        block => block.slotName === slotName
      ) as Array<any>;
      const newIndex = Math.max(Math.min(blockOnSlot.length - 1, orderNew), 0);

      blockOnSlot = blockOnSlot
        .sort((x, y) => x.blockOrder - y.blockOrder)
        .map(({ blockId }) => ({
          blockId
        }));

      const currentIndex = blockOnSlot.findIndex(x => x.blockId === blockId);

      const [movedObject] = blockOnSlot.splice(currentIndex, 1);

      blockOnSlot.splice(newIndex, 0, movedObject);

      blockOnSlot
        .map((x, index) => ({ ...x, blockOrder: index }))
        .forEach(({ blockId, blockOrder }) => {
          set(overrides, `${blockId}.blockOrder`, blockOrder);
        });

      page.overrides = overrides;

      this.afterEdit();
      // eslint-disable-next-line no-empty
    } catch (error) {}
  }

  public getBlockPresets(): Record<string, LayoutShape> {
    return this.blockPresets;
  }

  public normalizeDisplayingPresets<T extends ConfiguredLayout>({
    blockLayout,
    gridLayout,
    itemLayout
  }: T): T {
    let result = { blockLayout, itemLayout, gridLayout };

    if (blockLayout) {
      const styleProps = this.getBlockPreset(blockLayout);

      if (styleProps) {
        result = merge(result, styleProps);
      }
    }

    if (gridLayout) {
      const styleProps = this.getGridPreset(gridLayout);

      if (styleProps) {
        result = merge(result, styleProps);
      }
    }

    if (itemLayout) {
      const styleProps = this.getItemPreset(itemLayout);

      if (styleProps) {
        result = merge(result, styleProps);
      }
    }

    return result as T;
  }

  public getBlockPreset(name: string): LayoutShape | undefined {
    if (name) return this.blockPresets[name];
  }

  public setBlockPreset(name: string, data: LayoutShape): void {
    this.blockPresets[name] = data;
    this.setThemeDirty(true);
  }

  public getNoContentPresets(): Record<string, WithNoContentProps> {
    return this.noContentPresets;
  }

  public getNoContentPreset(name: string): WithNoContentProps {
    return this.noContentPresets[name];
  }

  public setNoContentPreset(name: string, value: Record<string, any>): void {
    this.noContentPresets[name] = value;
    this.setThemeDirty(true);
  }

  public getGridPresets(): Record<string, GridLayoutShape> {
    return this.gridPresets;
  }

  public getGridPreset(name: string): GridLayoutShape | undefined {
    if (name) return this.gridPresets[name];
  }

  public setGridPreset(name: string, data: GridLayoutShape) {
    this.setThemeDirty(true);
    this.gridPresets[name] = data;
  }

  public getItemPreset(name: string): ItemLayoutShape | undefined {
    if (name) return this.itemPresets[name];
  }

  public getItemPresets(): Record<string, ItemLayoutShape> {
    return this.itemPresets;
  }

  public setItemPreset(name: string, data: ItemLayoutShape) {
    this.setThemeDirty(true);
    this.itemPresets[name] = data;
  }

  public onEditTheme(props: any) {
    const x = this.manager?.dialogBackend;

    if (x) {
      x.present({
        component: 'layout.dialog.EditThemeDialog',
        props
      }).catch(err => {});
    }
  }

  public getVariant(): ThemeVariant {
    return this.variant;
  }

  /**
   * Merge origin and variant config
   *
   * @returns ThemeVariant
   */
  public getThemeConfig() {
    return merge({}, this.originVariant, this.variant);
  }

  public getThemeConfigForParticularMode(mode: string) {
    const derived = merge({}, this.originVariant, this.variant);

    if (mode === 'dark') {
      return merge({}, derived['default'], derived['dark']);
    }

    return derived['default'];
  }

  public resetVariant(): void {
    this.setVariant({ default: {}, dark: {} });
    this.setVariantDirty(true);
  }

  public setVariant(variant: ThemeVariant): void {
    const { eventCenter } = this.manager;
    this.variant = variant;
    eventCenter.dispatch('onThemeChanged', this.getThemeConfig());
  }

  public setupVariant(loaded: LoadedVariant) {
    this.variantInfo = loaded.info;
    this.variant = loaded.default;
  }

  public afterThemeChanged() {
    const { eventCenter } = this.manager;
    this.clearCache();
    eventCenter.dispatch('onThemeChanged', this.getThemeConfig());
    this.reload();
  }

  public mergePageLayouts(a, b) {
    const response = {};

    if (isPlainObject(a))
      Object.keys(a).forEach(x => {
        response[x] = a[x];
      });

    if (isPlainObject(b))
      Object.keys(b).forEach(x => {
        response[x] = b.x;
      });

    return response;
  }

  public setupTheme({ info, views, default: config }: LoadedTheme) {
    const { themeProcessor, jsxBackend } = this.manager;

    this.themeInfo = info;

    jsxBackend.use(views);

    this.originSiteBlocks = config.originSiteBlocks;
    this.siteBlocks = Object.assign(
      {},
      config.originSiteBlocks,
      config.siteBlocks
    );
    this.originGridPresets = config.originGridLayouts;
    this.gridPresets = Object.assign(
      {},
      config.originGridLayouts,
      config.gridLayouts
    );
    this.originBlockPresets = config.originBlockLayouts;
    this.blockPresets = Object.assign(
      {},
      config.originBlockLayouts,
      config.blockLayouts
    );
    this.originNoContentPresets = config.originNoContentLayouts;
    this.noContentPresets = Object.assign(
      {},
      config.originNoContentLayouts,
      config.noContentLayouts
    );
    this.originItemPresets = config.originItemLayouts;
    this.itemPresets = Object.assign(
      {},
      config.originItemLayouts,
      config.itemLayouts
    );
    this.originTemplates = config.originTemplates;
    this.templates = Object.assign(
      {},
      config.originTemplates,
      config.templates
    );

    this.originPageLayouts = config.originPageLayouts;
    this.originIntergrationLayouts = config.originIntergrationLayouts;
    this.pageSizesPriority = config.pageSizesPriority;
    this.templateNameDefaults = config.templateNameDefaults;

    function customizer(obj, src) {
      if (isArray(obj?.blocks) && isArray(src?.blocks)) {
        return { ...obj, blocks: obj.blocks.concat(src.blocks) };
      }
    }

    mergeWith(
      this.originPageLayouts,
      this.originIntergrationLayouts,
      customizer
    );

    this.pageLayouts = Object.assign(
      {},
      config.originPageLayouts,
      config.pageLayouts
    );
    this.siteDocks = Object.keys(config.originSiteDocks);
    this.siteFixedDocks = Object.keys(config.originSiteFixedDocks || {});
    this.originVariant = config.styles;
    this.ready = true;

    if (isArray(config.processors)) {
      themeProcessor.setProcessors(config.processors);
    }
  }

  public getThemeInfo(): ThemeInfo {
    return this.themeInfo;
  }

  public getVariantInfo(): VariantInfo {
    return this.variantInfo;
  }

  public isDirty(): boolean {
    return this.themeDirty || this.variantDirty;
  }

  public isThemeDirty(): boolean {
    return this.themeDirty;
  }

  public canPublish(value: boolean): void {
    this.publishEnabled = value;
    const { eventCenter, localStore } = this.manager;

    localStore.set('layout.canPublish', value ? '1' : '');
    eventCenter.dispatch('layout.canPublish.changed', value);
  }

  public canSave(value: boolean): void {
    this.saveEnabled = value;
    const { eventCenter, localStore } = this.manager;

    localStore.set('layout.canSave', value ? '1' : '');
    eventCenter.dispatch('layout.canSave.changed', value);
  }

  public saved(): boolean {
    return !this.saveEnabled;
  }

  public published(): boolean {
    return !this.publishEnabled;
  }

  public setVariantDirty(dirty: boolean): void {
    this.variantDirty = dirty;
    const { eventCenter } = this.manager;

    // does not merge to dirty, their are separated value
    if (dirty) {
      this.canPublish(true);
      this.canSave(true);
    }

    eventCenter.dispatch('layout.dirty.changed', dirty);

    this.manager.setNavigationConfirm(dirty, {
      message: 'Discard current layout changes?'
    });
  }

  public isVariantDirty(): boolean {
    return this.variantDirty;
  }

  public revert(data: { name: string; content: any }[]) {
    const overrides = [
      'templates',
      'noContentLayouts',
      'gridLayouts',
      'pageLayouts',
      'itemLayouts',
      'siteBlocks',
      'blockLayouts',
      'variant',
      'originVariant'
    ];

    data.forEach(item => {
      if (overrides.includes(item.name)) {
        this[item.name] = item.content;
      }
    });

    this.afterThemeChanged();
    this.reload();
  }

  // should register navigation prompt
  public setThemeDirty(dirty: boolean): void {
    this.themeDirty = dirty;
    const { eventCenter } = this.manager;

    // does not merge to dirty, their are separated value
    if (dirty) {
      this.canPublish(true);
      this.canSave(true);
    }

    eventCenter.dispatch('layout.dirty.changed', dirty);

    this.manager.setNavigationConfirm(dirty, {
      message: 'Discard current layout changes?'
    });
  }

  public getSiteBlocks() {
    return this.siteBlocks;
  }

  private getDiff<T>(custom: T, origin: T): T {
    const response = {};

    Object.keys(custom).forEach(name => {
      if (!isEqualWith(origin[name], custom[name])) {
        response[name] = custom[name];
      }
    });

    return response as T;
  }

  private cloneJson<T>(obj: T): T {
    if (!obj) return {};

    return JSON.parse(JSON.stringify(obj)) as T;
  }
}
