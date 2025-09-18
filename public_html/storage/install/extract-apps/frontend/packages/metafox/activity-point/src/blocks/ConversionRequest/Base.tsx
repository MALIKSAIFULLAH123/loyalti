import {
  BlockViewProps,
  useGlobal,
  useIsMobile,
  useResourceAction,
  useResourceForm
} from '@metafox/framework';
import { styled, Box } from '@mui/material';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import { FormBuilder } from '@metafox/form';
import { whenParamRules } from '@metafox/utils';
import qs from 'querystring';
import {
  APP_ACTIVITY,
  RESOURCE_POINT_CONVERSION_REQUEST,
  CONVERSION_REQUEST_GRID_NAME
} from '../../constants';
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

const TableStyled = styled(Box)(({ theme }) => ({
  [theme.breakpoints.up('sm')]: {
    overflowX: 'auto'
  }
}));

const RecordStyled = styled(Box, { name: 'RecordStyled' })(({ theme }) => ({}));

export default function Base({ title, ...rest }: Props) {
  const { usePageParams, navigate, jsxBackend } = useGlobal();
  const pageParams = usePageParams();
  const isMobile = useIsMobile();

  const dataSource = useResourceAction(
    APP_ACTIVITY,
    RESOURCE_POINT_CONVERSION_REQUEST,
    'viewAll'
  );

  const dataSourceGrid = useResourceAction(
    APP_ACTIVITY,
    RESOURCE_POINT_CONVERSION_REQUEST,
    'getGrid'
  );

  const formSchema = useResourceForm(
    APP_ACTIVITY,
    RESOURCE_POINT_CONVERSION_REQUEST,
    'search_form'
  );

  const ListView = jsxBackend.get('core.block.mainListing');

  const submitFilter = (values, form) => {
    const apiRules = dataSource.apiRules;

    const params = whenParamRules(values, apiRules);

    navigate(`?${qs.stringify(params)}`, { replace: true });
    form.setSubmitting(false);
  };

  return (
    <Block testid="activityPointBlock" {...rest}>
      <BlockHeader title={title}></BlockHeader>
      <BlockContent {...rest}>
        <ContentWrapper>
          <FormBuilder
            navigationConfirmWhenDirty={false}
            formSchema={formSchema}
            onSubmit={submitFilter}
          />
          <TableStyled>
            {!isMobile ? (
              <SmartDataGrid
                dataSource={dataSourceGrid}
                gridName={CONVERSION_REQUEST_GRID_NAME}
                errorComponent={ErrorBoundary}
              />
            ) : (
              <RecordStyled sx={isMobile && { mt: 3 }}>
                {React.createElement(ListView, {
                  itemView: 'activitypoint.itemView.conversion',
                  dataSource,
                  emptyPage: 'core.itemView.no_content_history_point',
                  pageParams,
                  clearDataOnUnMount: true,
                  blockLayout: 'App List - Record Table',
                  itemLayout: 'Record Item - Table',
                  gridLayout: 'Record Item - Table'
                })}
              </RecordStyled>
            )}
          </TableStyled>
        </ContentWrapper>
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'package_transaction';
