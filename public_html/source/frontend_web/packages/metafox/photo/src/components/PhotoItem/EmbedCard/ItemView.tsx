import { useGlobal } from '@metafox/framework';
import { EmbedPhotoInFeedItemProps } from '@metafox/photo/types';
import * as React from 'react';

export default function EmbedPhotoInFeedItemView({
  item,
  itemView = 'feedPhotoGrid.embedItem.insideFeedItem',
  feed,
  isShared
}: EmbedPhotoInFeedItemProps) {
  const { jsxBackend } = useGlobal();
  const isUpdateAvatar =
    feed?.type_id === 'user_update_avatar' || item?.photo_type === 'avatar';
  const itemViewParse = isUpdateAvatar
    ? 'feedUpdateAvatar.embedItem.insideFeedItem'
    : itemView;
  const ItemView = jsxBackend.get(itemViewParse);
  const props = isUpdateAvatar
    ? { ownerPhotoIdentity: item.owner }
    : {
        photos: [item],
        total_photo: 1
      };

  if (!ItemView || !item) return null;

  return (
    <ItemView
      isShared={isShared}
      data-testid={'embedview'}
      item={item}
      feed={feed}
      {...props}
    />
  );
}
