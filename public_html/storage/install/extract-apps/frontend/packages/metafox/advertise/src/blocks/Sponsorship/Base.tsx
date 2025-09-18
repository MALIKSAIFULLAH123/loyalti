import {
  BlockViewProps,
  useGlobal,
  useResourceAction,
  useResourceForm
} from '@metafox/framework';
import { styled, Box } from '@mui/material';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import { FormBuilder } from '@metafox/form';
import { whenParamRules } from '@metafox/utils';
import qs from 'querystring';
import { APP_NAME, RESOURCE_SPONSOR } from '@metafox/advertise/constants';
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
  [theme.breakpoints.up('md')]: {
    position: 'relative',
    overflowX: 'auto',
    width: '100%'
  }
}));

export default function Base({ title, ...rest }: Props) {
  const { usePageParams, navigate, jsxBackend, useIsMobile } = useGlobal();
  const pageParams = usePageParams();
  const isMobile = useIsMobile();

  const dataSource = useResourceAction(APP_NAME, RESOURCE_SPONSOR, 'getGrid');
  const dataSourceViewAll = useResourceAction(
    APP_NAME,
    RESOURCE_SPONSOR,
    'viewAll'
  );

  const formSchema = useResourceForm(APP_NAME, RESOURCE_SPONSOR, 'search_form');

  const ListView = jsxBackend.get('core.block.mainListing');

  const submitFilter = (values, form) => {
    const apiRules = dataSourceViewAll.apiRules;

    const params = whenParamRules(values, apiRules);

    navigate(`?${qs.stringify(params)}`, { replace: true });
    form.setSubmitting(false);
  };

  if (isMobile) {
    return (
      <Block testid="advertiseBlock" {...rest}>
        <BlockHeader title={title}></BlockHeader>
        <BlockContent {...rest}>
          <ContentWrapper>
            <FormBuilder
              navigationConfirmWhenDirty={false}
              formSchema={formSchema}
              onSubmit={submitFilter}
            />
            {React.createElement(ListView, {
              itemView: 'advertise.itemView.sponsorshipRecord',
              dataSource,
              emptyPage: 'advertise.itemView.no_content_record',
              pageParams,
              blockLayout: 'Large Main Lists',
              gridContainerProps: { spacing: 0 }
            })}
          </ContentWrapper>
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block testid="advertiseBlock" {...rest}>
      <BlockHeader title={title}></BlockHeader>
      <BlockContent {...rest}>
        <ContentWrapper>
          <FormBuilder
            navigationConfirmWhenDirty={false}
            formSchema={formSchema}
            onSubmit={submitFilter}
          />
          <GridWrapper>
            <SmartDataGrid
              dataSource={dataSource}
              gridName={'advertise.sponsor'}
              errorComponent={ErrorBoundary}
            />
          </GridWrapper>
        </ContentWrapper>
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'Advertise';
