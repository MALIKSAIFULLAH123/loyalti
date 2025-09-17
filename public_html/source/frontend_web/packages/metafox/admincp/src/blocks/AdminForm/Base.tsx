import {
  useGlobal,
  useResourceAction,
  useResourceForm
} from '@metafox/framework';
import { SmartFormBuilder } from '@metafox/form';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { UIBlockViewProps } from '@metafox/ui';
import { Alert } from '@mui/material';
import React from 'react';
import LoadingComponent from './LoadingComponent';

export interface Props extends UIBlockViewProps {
  appName: string;
  resourceName: string;
  formName: string;
  loadFrom: 'apiUrl' | 'formName' | 'pageParams';
  minHeight?: string | number;
  actionName?: string;
}

export default function AdminForm({
  title,
  dataSource: dataSourceProps,
  loadFrom,
  appName,
  resourceName,
  minHeight,
  formName,
  actionName,
  noHeader,
  ...props
}: Props) {
  const { usePageParams } = useGlobal();

  const isRemote = loadFrom !== 'formName';
  const formSchema = useResourceForm(appName, resourceName, formName);
  const dataSourceConfig = useResourceAction(appName, resourceName, actionName);
  const pageParams = usePageParams();
  const dataSource =
    dataSourceConfig ?? pageParams.dataSource ?? dataSourceProps;

  if (!dataSource && !formSchema) {
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
        <SmartFormBuilder
          noTitle
          pageParams={pageParams}
          formSchema={isRemote ? undefined : formSchema}
          dataSource={isRemote ? dataSource : undefined}
          loadingComponent={LoadingComponent as any}
          {...props}
        />
      </BlockContent>
    </Block>
  );
}
