import { useGlobal } from '@metafox/framework';
import * as React from 'react';
import { EmbedPhotoAlbumInFeedItemProps } from '../../../types';

export default function EmbedPhotoAlbumInFeedItemView({
  item,
  feed,
  photos,
  itemView = 'feedPhotoGrid.embedItem.insideFeedItem',
  setVisible,
  isShared
}: EmbedPhotoAlbumInFeedItemProps) {
  const { jsxBackend } = useGlobal();
  const ItemView = jsxBackend.get(itemView);

  if (!ItemView || !item) return null;

  const { statistic, id } = item;
  const { total_item } = statistic || {};

  const filteredPhotos = photos.filter(item => !item.error);

  if (!filteredPhotos.length && setVisible) setVisible(false);

  return (
    <ItemView
      item={item}
      photo_album={id}
      photos={filteredPhotos}
      total_item={total_item}
      data-testid="embedview"
      feed={feed}
      isShared={isShared}
    />
  );
}
