import { useGlobal } from '@metafox/framework';
import * as React from 'react';
import { EmbedPhotoSetInFeedItemProps } from '../../../types';

export default function EmbedPhotoSetInFeedItemView({
  item,
  feed,
  photos: photosProps,
  itemView = 'feedPhotoGrid.embedItem.insideFeedItem',
  isShared,
  setVisible
}: EmbedPhotoSetInFeedItemProps) {
  const { jsxBackend } = useGlobal();
  const ItemView = jsxBackend.get(itemView);
  const ItemViewNotFound = jsxBackend.get(
    'itemNotFound.embedItem.insideFeedItem'
  );

  if (!ItemView || !item) return null;

  const { id, remain_photo: remain_photo_props, photos: photosRaw } = item;
  const photos = photosProps || photosRaw;

  const remain_photo = remain_photo_props ?? photos.length;

  if ((!photos || !photos.length) && setVisible) {
    setVisible(false);
  }

  const errors = photos.filter(item => item.error);

  if (
    (!photos.length || (photos.length === 1 && errors.length === 1)) &&
    ItemViewNotFound
  ) {
    const photoError = photos[0];

    return (
      <ItemViewNotFound
        description={photoError?.message}
        title={
          photoError?.image ? 'content_is_not_available' : photoError?.title
        }
        sx={{ mt: 1.5 }}
      />
    );
  }

  return (
    <ItemView
      item={item}
      feed={feed}
      photo_set={id}
      photos={photos}
      total_photo={remain_photo}
      data-testid="embedview"
      isShared={isShared}
    />
  );
}
