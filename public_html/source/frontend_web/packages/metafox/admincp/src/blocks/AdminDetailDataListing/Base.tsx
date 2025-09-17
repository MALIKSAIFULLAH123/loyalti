import { useGlobal, useResourceAction } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { UIBlockViewProps } from '@metafox/ui';
import { Box } from '@mui/material';
import React from 'react';
import RowItem from './RowItem';

export interface Props extends UIBlockViewProps {}

export default function AdminDetailDataListing({ blockProps, title }: Props) {
  const { useFetchDetail, usePageParams, BlockLoading } = useGlobal();
  const { dataGridProps, id } = usePageParams();

  const { appName, resourceName, getListingActionName } = Object.assign(
    {},
    dataGridProps
  );

  const dataSource = useResourceAction(
    appName,
    resourceName,
    getListingActionName
  );
  const [data, loading, error] = useFetchDetail({
    dataSource,
    data: [],
    allowRiskParams: false,
    pageParams: {
      id
    }
  });

  if (!dataSource) return null;

  if (loading || error) {
    return (
      <Block>
        <BlockContent style={{ minHeight: '60vh' }}>
          {loading || error ? (
            <BlockLoading loading={loading} error={error} />
          ) : null}
        </BlockContent>
      </Block>
    );
  }

  if (!data || !data.length) return null;

  return (
    <Block>
      <BlockContent>
        <Box>
          {data.map((item, index) => (
            <RowItem key={`i${index}`} data={item} />
          ))}
        </Box>
      </BlockContent>
    </Block>
  );
}
