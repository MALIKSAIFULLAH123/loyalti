import {
  BlockViewProps,
  useGlobal,
  useResourceAction,
  useResourceForm
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import { FormBuilder } from '@metafox/form';
import { whenParamRules } from '@metafox/utils';
import qs from 'querystring';
import { RESOURCE_NAME, APP_NAME } from '@metafox/advertise/constants';

export type Props = BlockViewProps;

export default function Base({ title, ...rest }: Props) {
  const { usePageParams, navigate, jsxBackend } = useGlobal();
  const pageParams = usePageParams();

  const dataSource = useResourceAction(APP_NAME, RESOURCE_NAME, 'viewAll');

  const formSchema = useResourceForm(APP_NAME, RESOURCE_NAME, 'search_form');

  const ListView = jsxBackend.get('core.block.mainListing');

  const submitFilter = (values, form) => {
    const apiRules = dataSource.apiRules;

    const params = whenParamRules(values, apiRules);

    navigate(`?${qs.stringify(params)}`, { replace: true });
    form.setSubmitting(false);
  };

  return (
    <Block testid="advertiseBlock" {...rest}>
      <BlockHeader title={title}></BlockHeader>
      <BlockContent {...rest}>
        <FormBuilder
          navigationConfirmWhenDirty={false}
          formSchema={formSchema}
          onSubmit={submitFilter}
        />
        {React.createElement(ListView, {
          itemView: 'advertise.itemView.addAdsMobileRecord',
          dataSource,
          emptyPage: 'advertise.itemView.no_content_record',
          blockLayout: 'Large Main Lists',
          pageParams,
          gridContainerProps: { spacing: 0 }
        })}
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'Advertise';
