import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { GridItem } from '@metafox/ui';
import React from 'react';
import { FeedItemViewProps } from '../../types';

export type Props = BlockViewProps & FeedItemViewProps;

export default function FeedDetail({
  item,
  identity,
  user,
  itemProps,
  itemView
}: Props) {
  const { jsxBackend } = useGlobal();
  const FeedItemView = jsxBackend.get(itemView);

  if (!item || !FeedItemView) return null;

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <FeedItemView
          item={item}
          identity={identity}
          user={user}
          wrapAs={GridItem}
          itemProps={itemProps}
          isDetailFeed
        />
      </BlockContent>
    </Block>
  );
}
