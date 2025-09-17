import {
  AclShape,
  MenuShape,
  RemoteDataSource,
  SiteSettingShape
} from '@metafox/framework';
import { get, isFunction } from 'lodash';
import isCreator from './isCreator';

export interface RootConfig {}

export interface ManagerConfig {
  root?: RootConfig;
  services?: any;
}

export interface AppResourceAction extends RemoteDataSource {
  // required when action need to redirected to new url.
  pageUrl?: string;

  // required when action need to redirected to new url with some params default.
  pageParams?: Record<string, any>;

  // required whenever request api
  apiUrl: string;

  // optional whenever need new url

  apiMethod?: 'get' | 'post' | 'put' | 'delete' | 'patch';

  placeholder?: string;

  apiParams?: Record<string, any>;

  apiRules?: Record<string, any>;

  pageRules?: Record<string, any>;
}

type ActionName =
  | 'viewItem'
  | 'deleteItem'
  | 'updateItem'
  | 'editItem'
  | 'sponsorItem'
  | 'featureItem'
  | string;

type MenuName = 'itemActionMenu' | 'detailActionMenu' | string;

export interface AppResource {
  idField?: string; // default id
  name?: string;

  actions?: Record<ActionName, AppResourceAction>;

  menus?: Record<MenuName, MenuShape>;
}

export interface GlobalState {
  bootstrap?: {
    loaded: boolean;
    error?: string;
  };
}

/**
 * Preserved keyword could not be v
 */
const Preserved: string[] = [
  'use',
  'deps',
  'make',
  'manager',
  'bootstrap',
  'proxy',
  'fallbacks',
  'allConfig',
  'keepServices'
];

export class Manager {
  public manager: Manager;

  /**
   * store permission here.
   */
  public acl: AclShape;

  private _assets: Record<string, string> = {};

  public setting: SiteSettingShape;

  private settings: Record<string, any> = {};

  private readonly allConfig: Partial<ManagerConfig>;

  private readonly keepServices: Record<string, any> = {};

  /**
   * fallback services is helpful for writing test mock.
   */
  public static fallbacks: Record<string, any> = {};

  proxy: Manager;

  constructor(config: Partial<ManagerConfig>) {
    this.manager = this as any;
    this.allConfig = config;
    this.acl = {};
    this.setting = {};

    // prevent undefined "this" context
    this.getAcl = this.getAcl.bind(this);
    this.setAcl = this.setAcl.bind(this);
    this.getSetting = this.getSetting.bind(this);
    this.setSetting = this.setSetting.bind(this);
    this.setAssets = this.setAssets.bind(this);
    this.assetUrl = this.assetUrl.bind(this);
    this.use = this.use.bind(this);
    this.getConfig = this.getConfig.bind(this);
  }

  public static factory(config: Partial<ManagerConfig>) {
    return new Manager(config).bootstrap();
  }
  /**
   *
   * @param {string} name - make a service instance
   * @returns
   */
  private make(name: string) {
    if (this[name]) {
      // service is exists
      return;
    }

    let creator = this.keepServices[name];

    if (!creator) {
      // console.log(`fallbacks[${name}]: ${typeof Manager.fallbacks[name]}`);
      creator = Manager.fallbacks[name];
    }

    if (!creator) {
      return;
    }

    if (!isCreator(creator)) {
      this[name] = creator;

      return;
    }

    const configName = creator.configKey ?? name;
    const instance = new creator(get(this.allConfig, configName, {}));

    if (!isFunction(instance.bootstrap)) {
      throw new Error(`${name}.bootstrap is not callable`);
    }

    const service = instance.bootstrap(this.proxy);

    this[name] = service ? service : instance;
  }

  /**
   * test dependencies
   * @param {string[]} packages - list require packages
   */
  public deps(...packages: string[]): void {
    packages.forEach(name => this.make(name));
  }

  /**
   *
   * @param obj - a dictionary of service
   * @returns  void
   */
  public use(obj: Record<string, any>): Manager {
    if (obj) {
      Object.keys(obj).forEach(name => {
        const creator = obj[name] as any;

        if (Preserved.includes(name)) {
          throw new Error(`Duplicated preserved name ${Preserved.join(',')}`);
        } else if (isCreator(creator)) {
          if (this[name]) {
            throw new Error('Could not re-assign dependency at runtime.');
          }

          this.keepServices[name] = creator;
        } else {
          this[name] = creator;
        }
      });
    }

    return this.proxy;
  }

  /**
   * Overwrite data to acl data
   * @param {AclShape} data
   */
  public setAcl(data: AclShape) {
    this.acl = data;
  }

  /**
   *
   * @param {string} name - any name separated by ".", check lodash.get
   * @param {any} value - default value
   * @returns {any}
   */
  public getAcl<T = boolean>(name?: string, value?: T): T {
    if (!name) {
      return this.acl as any;
    }

    return get(this.acl as any, name, value);
  }

  /**
   *
   * @param {string} data
   */
  public setSetting(data: SiteSettingShape) {
    this.setting = Object.assign({}, this.settings, data);
  }

  public setAssets(data: Record<string, string>) {
    this._assets = data;
  }

  public assetUrl(name: string): string {
    return get(this._assets, name);
  }

  public getSetting<T = string>(name?: string, value?: T): T {
    if (!name) {
      return this.setting as any;
    }

    return get(this.setting, name, value);
  }

  /**
   * Bootstrap
   *
   * @returns Manager
   */
  private bootstrap(): Manager {
    if (this.proxy) {
      return this.proxy;
    }

    const handler = {
      get(manager: Manager, name: string) {
        if (manager[name]) {
          return manager[name];
        }

        manager.make(name);

        return manager[name];
      }
    };

    this.proxy = new Proxy(this, handler);

    return this.proxy;
  }

  /**
   *
   * @param {string} name - name of configure, use "." separator
   * @returns any
   */
  public getConfig<T>(name: string, defaultValue = undefined): T {
    return get(this.allConfig, name, defaultValue);
  }
}
