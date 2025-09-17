/**
 * @type: route
 * name: core.admincp.maintain
 * path: /maintain/:tab(cache|duplicated-files|system-overview|site-statistic|crontab|reparser|counter|missed-settings)
 * chunkName: pages.admincp
 * bundle: admincp
 * priority: 9999
 */
import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { normalizePageName } from '@metafox/utils';
import { uniq } from 'lodash';
import React from 'react';

const makeGridName = (app: string, resource: string, name?: string): string => {
  return uniq(`${app}.${resource}${name ? `.${name}` : ''}`.split('.')).join(
    '.'
  );
};

const modifiedName = (name: string): string => name?.replace(/-/g, '_');

export default function AdminMaintain(props) {
  const { createPageParams } = useGlobal();
  const tab = props?.match?.params?.tab || 'cache';

  const pageParams = createPageParams<{
    readonly name: string;
    readonly resourceName: string;
    readonly appName: string;
    readonly gridName: string;
  }>(
    props,
    ({ appName = 'core', resourceName, name }) => ({
      gridName: makeGridName(appName, tab),
      pageMetaName: normalizePageName(appName, tab, 'maintain')
    }),
    ({ appName = 'core', gridName, resourceName }) => ({
      dataGridProps: {
        title: 'Category',
        appName: modifiedName(appName),
        resourceName: modifiedName(resourceName),
        gridName
      }
    })
  );

  return (
    <Page pageName={`core.admincp.maintain.${tab}`} pageParams={pageParams} />
  );
}
