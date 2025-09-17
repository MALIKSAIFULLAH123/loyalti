/**
 * @type: service
 * name: jsxBackend
 */
import { RenderBaseItem, RenderItemProps } from '@metafox/framework';
import { isString } from 'lodash';
import React from 'react';
import { isValidElementType } from 'react-is';
import { JsxBackendConfig } from './types';

/**
 * JsxBackend provider virtual component map by name => element type
 *
 */
export default class JsxBackend {
  /**
   * Configuration key.
   */
  public static readonly configKey: string = 'views';

  /**
   * store all views
   */
  private data: JsxBackendConfig;

  /**
   * default constructor
   * @constructor
   */
  constructor(data: JsxBackendConfig) {
    this.data = data;
    this.use = this.use.bind(this);
  }

  /**
   * @internal
   * this method is private and called by Manager
   */
  public bootstrap() {}

  /**
   * Extending current configure
   *
   * @param {JsxBackendConfig} opts - a dictionary of Jsx Component
   */
  public use(opts: JsxBackendConfig) {
    Object.keys(opts).forEach(key => {
      this.data[key] = opts[key];
    });
  }

  public get<T = any>(
    tagName: string | React.ElementType
  ): React.ElementType<T> {
    return isString(tagName) && this.data[tagName]
      ? this.data[tagName]
      : undefined;
  }

  public find(fn: (key: string) => boolean): string[] {
    return Object.keys(this.data).filter(fn);
  }

  /**
   * Get all jsx component map
   * @returns
   */
  public getAll(): JsxBackendConfig {
    return this.data;
  }

  /**
   *
   * @param tagName - name of component or tags
   * @returns boolean
   */
  public has(tagName: any): boolean {
    return 'string' === typeof tagName && this.data[tagName] !== undefined;
  }

  /**
   *
   * @param {RenderItemProps} item  - array or single render item
   * @param {Function} filter (optional) - filter
   * @returns -  Jsx Element
   */
  public render(
    item: RenderItemProps,
    filter?: (c: RenderBaseItem, index: number) => boolean
  ) {
    if (Array.isArray(item)) {
      if (filter) {
        return item.filter(filter).map(c => this.render(c));
      } else {
        return item.map(c => this.render(c)).filter(Boolean);
      }
    } else if (item?.component) {
      let { component: tag } = item;
      const { props } = item;

      if ('string' === typeof tag && this.data[tag]) {
        tag = this.data[tag];
      } else if (!isValidElementType(tag)) {
        return null;
      } else if ('string' === typeof tag) {
        if (!/div|span|p|h1|h2|h3|h4|h5|h6|b|i|img/i.test(tag)) return null;
      }

      return React.createElement(tag, props);
    }

    return null;
  }
}
