import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';
import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { APP_GROUP } from '@metafox/group/constant';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { Box, Skeleton } from '@mui/material';
import { isArray, isEmpty } from 'lodash';
import React from 'react';
import ItemRule from './ItemRule';

export interface Props extends BlockViewProps {}

export default function UserProfileRulesBlock({
  title,
  identity
}: Props & { identity: string }) {
  const { useFetchDetail, usePageParams, jsxBackend, useGetItem } = useGlobal();
  const item = useGetItem(identity) || [];
  const dataSource = useResourceAction(APP_GROUP, 'group_rule', 'viewAll');
  const pageParams = usePageParams();

  const apiParams = {
    group_id: item.id
  };
  const [data, loading, error] = useFetchDetail({
    dataSource: { ...dataSource, apiParams },
    pageParams
  });

  if (isEmpty(data) || !isArray(data) || error) return null;

  const LoadingSkeleton = () => (
    <Box>
      <Skeleton width={'100%'} />
      <Skeleton width={'100%'} />
      <Skeleton width={'100%'} />
    </Box>
  );

  const NoContentBlock = jsxBackend.get('core.block.no_content');

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <ErrorBoundary
          loading={loading}
          error={error}
          loadingComponent={LoadingSkeleton}
          emptyComponent={data ? null : NoContentBlock}
        >
          {data.map((item, index) => (
            <ItemRule key={item.id} index={index} {...item} />
          ))}
        </ErrorBoundary>
      </BlockContent>
    </Block>
  );
}
