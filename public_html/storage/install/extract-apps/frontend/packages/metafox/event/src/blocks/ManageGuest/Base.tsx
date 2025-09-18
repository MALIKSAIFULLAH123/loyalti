import { BlockViewProps, useGlobal, useResourceAction } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { RemoteDataSource } from '@metafox/ui';
import React from 'react';

const gridContainerProps = { spacing: 0 };

export type Props = BlockViewProps;

export default function ManageGuest({ title, blockProps }: Props) {
  const { usePageParams, ListView, compactUrl } = useGlobal();
  const { id } = usePageParams();
  const config = useResourceAction('event', 'event_invite', 'viewAll');
  const dataSource: RemoteDataSource = {
    apiUrl: compactUrl(config.apiUrl, {}),
    apiParams: { event_id: id }
  };
  const pagingId = `/event_invite/${id}`;

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <ListView
          dataSource={dataSource}
          pagingId={pagingId}
          canLoadMore
          clearDataOnUnMount
          gridContainerProps={gridContainerProps}
          itemView="event_invite.itemView.mainCard"
        />
      </BlockContent>
    </Block>
  );
}
