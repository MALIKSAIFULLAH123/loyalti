/**
 * @type: block
 * name: core.block.AdminBrowseSearchForm
 * title: AdminCP - Search Form
 * bundle: admincp
 */

import { createBlock, useGlobal, useResourceAction } from '@metafox/framework';
import React from 'react';
import { Block, BlockContent } from '@metafox/layout';
import { SmartFormBuilder } from '@metafox/form';

export function AdminBrowseSearchForm() {
  const { usePageParams } = useGlobal();
  const {
    dataGridProps = {},
    appName: appNameOrigin,
    resourceName: resourceNameOrigin
  } = usePageParams();
  const { appName, resourceName, actionName } = Object.assign(
    {},
    dataGridProps
  );
  const dataSourceFromGrid = useResourceAction(
    appName,
    resourceName,
    actionName || 'searchForm'
  );
  // API not compatible '-' and '_'
  const dataSourceOrigin = useResourceAction(
    appNameOrigin,
    resourceNameOrigin,
    actionName || 'searchForm'
  );

  const dataSource = dataSourceFromGrid || dataSourceOrigin;

  if (!dataGridProps || !dataSource) {
    return null;
  }

  return (
    <Block>
      <BlockContent style={{ minHeight: 0 }}>
        <SmartFormBuilder dataSource={dataSource} />
      </BlockContent>
    </Block>
  );
}

export default createBlock({
  extendBlock: AdminBrowseSearchForm,
  defaults: {
    blockLayout: 'Admin Form'
  }
});
