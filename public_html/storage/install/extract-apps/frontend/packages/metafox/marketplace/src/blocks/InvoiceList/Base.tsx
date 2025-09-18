import {
  BlockViewProps,
  useGlobal,
  useIsMobile,
  useResourceAction
} from '@metafox/framework';
import { styled, Box } from '@mui/material';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import { RemoteFormBuilder } from '@metafox/form';
import { whenParamRules } from '@metafox/utils';
import qs from 'querystring';
import {
  APP_MARKETPLACE,
  RESOURCE_INVOICE,
  STAB_BOUGHT
} from '@metafox/marketplace/constants';
import { SmartDataGrid } from '@metafox/ui/Loadable';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';

export type Props = BlockViewProps;

const TableStyled = styled(Box)(({ theme }) => ({
  [theme.breakpoints.up('sm')]: {
    overflowX: 'auto'
  }
}));

const ContentWrapper = styled(Box, {
  name: 'ContentWrapper'
})(({ theme }) => ({
  padding: theme.spacing(3, 2, 2),
  [theme.breakpoints.down('md')]: {
    padding: theme.spacing(0)
  }
}));

export default function Base({ title = 'all_invoices', ...rest }: Props) {
  const { usePageParams, navigate, jsxBackend } = useGlobal();
  const pageParams = usePageParams();
  const isMobile = useIsMobile();

  const dataSource = useResourceAction(
    APP_MARKETPLACE,
    RESOURCE_INVOICE,
    'viewAll'
  );

  const dataSourceGrid = useResourceAction(
    APP_MARKETPLACE,
    RESOURCE_INVOICE, 
    pageParams?.view === STAB_BOUGHT ?
    'getBoughtGird' : 'getSoldGrid'
  );

  const dataSourceSearch = useResourceAction(
    APP_MARKETPLACE,
    RESOURCE_INVOICE,
    pageParams?.view === STAB_BOUGHT
      ? 'getBoughtSearchForm'
      : 'getSoldSearchForm'
  );

  const ListView = jsxBackend.get('core.block.mainListing');

  const submitFilter = (values, form) => {
    const apiRules = dataSource.apiRules;

    const params = whenParamRules(values, apiRules);

    navigate(`?${qs.stringify({ ...params })}`, {
      replace: true
    });
    form.setSubmitting(false);
  };

  if (isMobile) {
    return (
      <Block testid="invoiceBoughtBlock" {...rest}>
        <BlockHeader title={title}></BlockHeader>
        <BlockContent {...rest}>
          <ContentWrapper>
            <RemoteFormBuilder
              navigationConfirmWhenDirty={false}
              dataSource={dataSourceSearch}
              onSubmit={submitFilter}
            />
            <Box>
              {React.createElement(ListView, {
                itemView: 'marketplace_invoice.itemView.invoice',
                dataSource,
                emptyPage: 'core.itemView.no_content_history_point',
                emptyPageProps: {
                  noBlock: true
                },
                pageParams,
                clearDataOnUnMount: true,
                blockLayout: 'App List - Record Table - No Title',
                gridContainerProps: { spacing: 0 }
              })}
            </Box>
          </ContentWrapper>
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block testid="invoiceBoughtBlock" {...rest}>
      <BlockHeader title={title}></BlockHeader>
      <BlockContent {...rest}>
        <ContentWrapper>
          <RemoteFormBuilder
            navigationConfirmWhenDirty={false}
            dataSource={dataSourceSearch}
            onSubmit={submitFilter}
          />
          <TableStyled>
            <SmartDataGrid
              dataSource={dataSourceGrid}
              gridName={`marketplace.${pageParams?.view}-invoice`}
              errorComponent={ErrorBoundary}
            />
          </TableStyled>
        </ContentWrapper>
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'package_transaction';
