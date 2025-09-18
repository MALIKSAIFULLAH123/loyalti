import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { styled, Box } from '@mui/material';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import { SmartDataGrid } from '@metafox/ui/Loadable';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';
import { APP_GROUP, RESOURCE_GROUP_INVITE } from '@metafox/group/constant';
import { SmartFormBuilder, RemoteFormBuilderProps } from '@metafox/form';
import qs from 'querystring';

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
  const { navigate } = useGlobal();
  const dataSource = useResourceAction(
    APP_GROUP,
    RESOURCE_GROUP_INVITE,
    'getGrid'
  );

  const dataSourceSearch = useResourceAction(
    APP_GROUP,
    RESOURCE_GROUP_INVITE,
    'searchForm'
  );

  const submitFilter = (values, form) => {
    navigate(`?${qs.stringify(values)}`, { replace: true });
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
          <GridWrapper>
            <SmartDataGrid
              dataSource={dataSource}
              gridName={'group.membership_invites'}
              errorComponent={ErrorBoundary}
            />
          </GridWrapper>
        </ContentWrapper>
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'Feature_Invoice';
