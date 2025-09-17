import { BlockViewProps, useGlobal } from '@metafox/framework';
import { SmartFormBuilder } from '@metafox/form';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import useStyles from './styles';

export interface Props extends BlockViewProps {
  initialValues: any;
}

export default function Base({ title, initialValues, blockProps }: Props) {
  const { useContentParams, usePageParams } = useGlobal();
  const classes = useStyles();
  const { mainForm } = useContentParams();
  const pageParams = usePageParams();

  if (!mainForm?.dataSource && !mainForm.formSchema) {
    return null;
  }

  const {
    dataSource,
    noHeader,
    noBreadcrumb,
    formSchema,
    breadcrumbs,
    minHeight,
    disableFormOnSuccess
  } = mainForm;

  return (
    <Block testid="mainForm">
      <BlockHeader title={title} />
      <BlockContent style={{ minHeight }}>
        <div className={classes.formWrapper}>
          <SmartFormBuilder
            noHeader={noHeader}
            breadcrumbs={breadcrumbs}
            noBreadcrumb={noBreadcrumb}
            formSchema={formSchema}
            pageParams={pageParams}
            dataSource={dataSource}
            onCancel={mainForm?.onCancel}
            initialValues={initialValues}
            changeEventName={mainForm.changeEventName}
            disableFormOnSuccess={disableFormOnSuccess}
            successAction={mainForm?.successAction}
          />
        </div>
      </BlockContent>
    </Block>
  );
}
