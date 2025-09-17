import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { styled, Box } from '@mui/material';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import { RemoteFormBuilderProps, SmartFormBuilder } from '@metafox/form';
import { whenParamRules } from '@metafox/utils';
import qs from 'querystring';
import {
  APP_NAME,
  RESOURCE_FEATURED_INVOICE
} from '@metafox/featured/constants';
import { SmartDataGrid } from '@metafox/ui/Loadable';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';

export type Props = BlockViewProps;

const ContentWrapper = styled(Box, {
  name: 'ContentWrapper'
})(({ theme }) => ({
  padding: theme.spacing(3, 2, 2),
  [theme.breakpoints.down('md')]: {
    padding: theme.spacing(0)
  }
}));
const GridWrapper = styled(Box, {
  name: 'GridWrapper'
})(({ theme }) => ({
  marginTop: theme.spacing(1.5)
}));

const LoadingComponent = (
  props: RemoteFormBuilderProps['loadingComponent']
) => <div />;

export default function Base({ title, ...rest }: Props) {
  const { navigate, isMobile, usePageParams, jsxBackend } = useGlobal();
  const pageParams = usePageParams();
  const ListView = jsxBackend.get('core.block.mainListing');

  const dataSource = useResourceAction(
    APP_NAME,
    RESOURCE_FEATURED_INVOICE,
    'getGrid'
  );

  const dataSourceSearch = useResourceAction(
    APP_NAME,
    RESOURCE_FEATURED_INVOICE,
    'getSearchForm'
  );

  const submitFilter = (values, form) => {
    const apiRules = dataSource.apiRules;

    const params = whenParamRules(values, apiRules);

    navigate(`?${qs.stringify(params)}`, { replace: true });
    form.setSubmitting(false);
  };

  return (
    <Block testid="featureInvoiceBlock" {...rest}>
      <BlockHeader title={title}></BlockHeader>
      <BlockContent {...rest}>
        <ContentWrapper>
          <SmartFormBuilder
            navigationConfirmWhenDirty={false}
            dataSource={dataSourceSearch}
            onSubmit={submitFilter}
            hideWhenError
            loadingComponent={LoadingComponent as any}
          />
          {isMobile ? (
            React.createElement(ListView, {
              itemView: 'featured.itemView.invoice',
              dataSource,
              emptyPage: 'featured.itemView.no_content_record',
              blockLayout: 'Large Main Lists',
              pageParams,
              gridContainerProps: { spacing: 0 }
            })
          ) : (
            <GridWrapper>
              <SmartDataGrid
                dataSource={dataSource}
                gridName={'featured.invoice'}
                errorComponent={ErrorBoundary}
              />
            </GridWrapper>
          )}
        </ContentWrapper>
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'Feature_Invoice';
