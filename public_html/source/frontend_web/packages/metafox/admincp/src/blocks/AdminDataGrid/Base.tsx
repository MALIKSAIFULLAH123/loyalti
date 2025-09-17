import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { UIBlockViewProps } from '@metafox/ui';
import { Alert } from '@mui/material';
import React from 'react';
import { SmartDataGrid } from '@metafox/ui/Loadable';

export interface Props extends UIBlockViewProps {
  dataGridProps: {
    appName?: string;
    resourceName?: string;
    gridName?: string;
  };
}

export default function AdminDataGrid({
  title,
  dataGridProps,
  blockProps
}: Props) {
  const { appName, resourceName, gridName = 'admin' } = dataGridProps ?? {};

  if (!dataGridProps) {
    return (
      <Block>
        <BlockHeader title={title} />
        <BlockContent>
          <Alert variant="standard" color="error">
            Oops!, could not find configuration.
          </Alert>
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent style={{ minHeight: 0 }}>
        <SmartDataGrid
          appName={appName}
          resourceName={resourceName}
          gridName={gridName}
        />
      </BlockContent>
    </Block>
  );
}
