import { useGlobal, useResourceAction } from '@metafox/framework';
import { SmartFormBuilder } from '@metafox/form';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { UIBlockViewProps } from '@metafox/ui';
import React from 'react';
import LoadingComponent from './LoadingComponent';

export interface Props extends UIBlockViewProps {
  appName: string;
  resourceName: string;
  formName: string;
  minHeight?: string | number;
  actionName?: string;
}

export default function AppSearchSettingForm({
  title,
  minHeight,
  formName,
  actionName,
  ...props
}: Props) {
  const { usePageParams } = useGlobal();

  const pageParams = usePageParams();
  const { appName, resourceName } = pageParams || {};
  const dataSourceConfig = useResourceAction(appName, resourceName, actionName);

  const dataSource = dataSourceConfig;

  if (!dataSource) {
    return null;
  }

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent style={{ minHeight }}>
        <SmartFormBuilder
          noTitle
          pageParams={pageParams}
          dataSource={dataSource}
          loadingComponent={LoadingComponent as any}
          {...props}
        />
      </BlockContent>
    </Block>
  );
}
