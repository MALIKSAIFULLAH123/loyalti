import {
  BlockViewProps,
  useGlobal,
  useIsMobile,
  useResourceAction
} from '@metafox/framework';
import { styled, Box, Typography, Grid } from '@mui/material';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import { RemoteFormBuilder } from '@metafox/form';
import { whenParamRules } from '@metafox/utils';
import qs from 'querystring';
import {
  APP_NAME,
  RESOURCE_INVOICE,
  STAB_BOUGHT,
  STAB_SOLD
} from '@ft/sevent/constants';

export type Props = BlockViewProps;

const gridTitle = [
  {
    label: 'sevent_campaigns',
    grid: 2.5
  },
  {
    label: 'sevent_ticket',
    grid: 1.5
  },
  {
    label: 'sevent_qty',
    grid: 0.5
  },
  {
    label: 'amount',
    grid: 1
  },
  {
    label: 'transaction_date',
    grid: 2
  },
  {
    label: 'payment_status',
    grid: 2
  },
  {
    grid: 2,
    showWhen: [
      { view: STAB_BOUGHT, label: 'seller' },
      { view: STAB_SOLD, label: 'buyer' }
    ]
  },
  {
    label: null,
    grid: 0.5,
    center: true
  }
];

const TableStyled = styled(Box)(({ theme }) => ({
  [theme.breakpoints.up('sm')]: {
    overflowX: 'auto'
  }
}));

const TitleStyled = styled(Grid, { name: 'TitleStyled' })(({ theme }) => ({
  display: 'flex',
  padding: theme.spacing(3, 2, 2),
  [theme.breakpoints.up('sm')]: {
    minWidth: theme.breakpoints.values.md
  }
}));

const RecordStyled = styled(Box, { name: 'RecordStyled' })(({ theme }) => ({
  [theme.breakpoints.up('sm')]: {
    minWidth: theme.breakpoints.values.md
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
  const { usePageParams, navigate, jsxBackend, i18n } = useGlobal();
  const pageParams = usePageParams();
  const isMobile = useIsMobile();

  const dataSource = useResourceAction(
    APP_NAME,
    RESOURCE_INVOICE,
    'viewAll'
  );

  const dataSourceSearch = useResourceAction(
    APP_NAME,
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

  console.log(gridTitle);

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
                itemView: 'sevent_invoice.itemView.invoice',
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
            <TitleStyled container>
              {gridTitle.map((item, index) => (
                <Grid item key={index} xs={item.grid}>
                  <Typography
                    variant="h5"
                    sx={item.center && { textAlign: 'center' }}
                  >
                    {item.label && i18n.formatMessage({ id: item.label })}
                    {item.showWhen &&
                      item.showWhen
                        .filter(item => item.view === pageParams?.view)
                        .map(item => i18n.formatMessage({ id: item.label }))}
                  </Typography>
                </Grid>
              ))}
            </TitleStyled>

            <RecordStyled>
              {React.createElement(ListView, {
                itemView: 'sevent_invoice.itemView.invoice',
                dataSource,
                emptyPage: 'core.itemView.no_content_history_point',
                emptyPageProps: {
                  noBlock: true
                },
                pageParams,
                clearDataOnUnMount: true,
                blockLayout: 'App List - Record Table - No Title',
                itemLayout: 'Record Item - Table',
                gridLayout: 'Record Item - Table'
              })}
            </RecordStyled>
          </TableStyled>
        </ContentWrapper>
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'package_transaction';
