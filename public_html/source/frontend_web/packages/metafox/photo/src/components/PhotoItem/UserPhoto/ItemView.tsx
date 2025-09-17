import { useGlobal } from '@metafox/framework';
import { EmbedPhotoInFeedItemProps } from '@metafox/photo/types';
import * as React from 'react';

export default function UserPhotoInFeedItemView({
  item,
  photo,
  itemView = 'feedPhotoGrid.embedItem.insideFeedItem',
  isShared
}: EmbedPhotoInFeedItemProps) {
  const { jsxBackend } = useGlobal();
  const ItemView = jsxBackend.get(itemView);

  if (!ItemView || !photo) return null;

  return (
    <ItemView
      photos={[photo]}
      item={item}
      total_photo={1}
      data-testid="embedview"
      isShared={isShared}
    />
  );
}
