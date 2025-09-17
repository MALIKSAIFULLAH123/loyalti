import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';
import { APP_FEED } from '@metafox/feed';
import { FormBuilder, FormSchemaShape } from '@metafox/form';
import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { Card } from '@mui/material';
import * as React from 'react';
import { AppState } from '../../types';
import { LoadingFormSkeleton } from './LoadingSkeleton';

const FormTagPost = () => {
  const { useFetchDetail, usePageParams } = useGlobal();
  const pageParams = usePageParams();

  const [formSchema, setFormSchema] = React.useState<FormSchemaShape>();

  const [data, loading, error] = useFetchDetail({
    dataSource: { apiUrl: '/user/account/review-form' },
    pageParams,
    allowRiskParams: false,
    forceReload: true,
    preventReload: false
  });

  React.useEffect(() => {
    if (data) {
      setFormSchema(data);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data]);

  return (
    <ErrorBoundary
      loading={loading}
      error={error}
      loadingComponent={LoadingFormSkeleton}
      emptyComponent={null}
    >
      <Card sx={{ p: 2, mb: 2, boxShadow: 'none' }}>
        <FormBuilder
          navigationConfirmWhenDirty={false}
          formSchema={formSchema}
        />
      </Card>
    </ErrorBoundary>
  );
};

export type Props = BlockViewProps & AppState['profilePrivacy'];

export default function ReviewPost({
  title,
  canLoadMore,
  gridVariant = 'listView',
  gridLayout,
  itemLayout,
  itemView,
  dataSource,
  ...rest
}: Props) {
  const { i18n, ListView, jsxBackend } = useGlobal();
  dataSource = useResourceAction(APP_FEED, APP_FEED, 'reviewTagStreams');

  const EmptyPage = jsxBackend.get('core.block.error404');

  if (!dataSource) return React.createElement(EmptyPage);

  return (
    <>
      <Block sx={{ mb: 0 }}>
        <BlockHeader title={i18n.formatMessage({ id: title })} />
        <BlockContent>
          <FormTagPost />
        </BlockContent>
      </Block>
      <ListView
        clearDataOnUnMount
        canLoadMore
        dataSource={{
          apiUrl: dataSource.apiUrl,
          apiParams: dataSource.apiParams
        }}
        gridVariant={gridVariant}
        gridLayout="User - PendingPost - Main Card"
        itemLayout="User - PendingPost - Main Card"
        itemView={itemView}
        emptyPage="core.block.no_content_with_icon"
        emptyPageProps={{
          title: i18n.formatMessage({ id: 'no_review_posts' }),
          image: 'ico-clock-o'
        }}
        {...rest}
      />
    </>
  );
}
