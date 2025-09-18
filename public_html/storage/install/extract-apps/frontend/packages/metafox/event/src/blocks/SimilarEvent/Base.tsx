import { APP_EVENT } from '@metafox/event';
import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import {
  Block,
  BlockContent,
  BlockHeader,
  usePageParams
} from '@metafox/layout';
import { useFetchDetail } from '@metafox/rest-client';
import React from 'react';

export type Props = BlockViewProps;

const SimilarEvent = ({
  title,
  gridVariant = 'listView',
  gridLayout,
  itemLayout,
  itemView,
  displayLimit,
  ...rest
}: Props) => {
  const { ListView } = useGlobal();

  const dataSource = useResourceAction(APP_EVENT, APP_EVENT, 'viewSimilar');
  const pageParams = usePageParams();
  const [data, , error] = useFetchDetail({
    dataSource,
    pageParams: { id: pageParams?.id }
  });

  if (!dataSource || !data?.length || error) return null;

  return (
    <Block {...rest}>
      <BlockHeader title={title} />
      <BlockContent>
        <ListView
          displayLimit={displayLimit}
          dataSource={dataSource}
          gridVariant={gridVariant}
          gridLayout={gridLayout}
          itemLayout={itemLayout}
          itemView={itemView}
        />
      </BlockContent>
    </Block>
  );
};

export default SimilarEvent;
