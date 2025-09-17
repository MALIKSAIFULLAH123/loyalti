/**
 * @type: block
 * name: core.block.AdminBrowseDataGrid
 * title: AdminCP - Main DataGrid
 * bundle: admincp
 */
import { Block, BlockContent } from '@metafox/layout';
import { UIBlockViewProps } from '@metafox/ui';
import { SmartDataGrid } from '@metafox/ui/Loadable';
import { Alert } from '@mui/material';
import React from 'react';
import { createBlock, useGlobal } from '@metafox/framework';

export interface Props extends UIBlockViewProps {}

export function AdminBrowseDataGrid(props) {
  const { usePageParams } = useGlobal();
  const { dataGridProps } = usePageParams();

  if (!dataGridProps) {
    return (
      <Block>
        <BlockContent>
          <Alert variant="standard" color="error">
            Oops!, could not find configuration.
          </Alert>
        </BlockContent>
      </Block>
    );
  }

  const { appName, resourceName, gridName = 'admin' } = dataGridProps;

  return (
    <Block>
      <BlockContent>
        <SmartDataGrid
          appName={appName}
          resourceName={resourceName}
          gridName={gridName}
          configTTL={5000}
        />
      </BlockContent>
    </Block>
  );
}

export default createBlock({
  extendBlock: AdminBrowseDataGrid,
  defaults: {
    title: 'Browse Data'
  }
});
