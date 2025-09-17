import { useGlobal } from '@metafox/framework';
import { SmartFormBuilder } from '@metafox/form';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { UIBlockViewProps } from '@metafox/ui';
import { Alert } from '@mui/material';
import React from 'react';
import LoadingComponent from './LoadingComponent';
import Tabs from './Tabs';

export interface Props extends UIBlockViewProps {
  appName: string;
  resourceName: string;
  formName: string;
  minHeight?: string | number;
  actionName?: string;
}

export default function AdminForm({
  title,
  dataSource: dataSourceProps,
  minHeight,
  formName,
  actionName,
  noHeader,
  ...props
}: Props) {
  const { usePageParams, useResourceMenu } = useGlobal();
  const pageParams = usePageParams();
  const { appName, resourceName } = pageParams;
  const tabConfig = useResourceMenu(appName, resourceName, 'tabsEditMenu');
  const dataSource = pageParams.dataSource ?? dataSourceProps;

  if (!dataSource && !tabConfig) {
    return (
      <Block>
        <BlockHeader title={title} />
        <BlockContent style={{ minHeight }}>
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
      <BlockContent style={{ minHeight }}>
        {tabConfig ? (
          <Tabs tabs={tabConfig?.items} />
        ) : (
          <SmartFormBuilder
            noTitle
            pageParams={pageParams}
            dataSource={dataSource}
            loadingComponent={LoadingComponent as any}
            {...props}
          />
        )}
      </BlockContent>
    </Block>
  );
}
