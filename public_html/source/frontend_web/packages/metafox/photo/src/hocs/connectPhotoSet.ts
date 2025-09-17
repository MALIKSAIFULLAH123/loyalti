import { connect, GlobalState } from '@metafox/framework';
import { findIndex, get } from 'lodash';
import { RESOURCE_ALBUM, RESOURCE_PHOTO_SET } from '@metafox/photo';

const getResourceContext = (photo_album, photo_set) => {
  if (photo_album) return [RESOURCE_ALBUM, photo_album];

  if (photo_set) return [RESOURCE_PHOTO_SET, photo_set];

  return [];
};

const shouldPreload = (total, offset, nearly = 3) => {
  if (offset < nearly) return 'prev';

  if (offset > total - nearly - 1) return 'next';

  return '';
};

const mapStateToProps = (
  state: GlobalState,
  { photo_set, photo_album, identity, photo_id, media_type }: any
) => {
  const item = get(state, identity);

  if (!item) {
    return {};
  }

  const [fromResource, fromResourceId] = getResourceContext(
    photo_album,
    photo_set
  );
  const data =
    get(state, `photo.entities.${fromResource}.${fromResourceId}.photos`) || [];
  const photos = data.filter(item => {
    const _item = get(state, item);

    if (!_item.error) return item;
  });
  const user = item?.user ? get(state, item.user) : undefined;
  const currentTotal = photos?.length;
  const pos =
    1 < currentTotal ? findIndex(photos, (x: string) => x === identity) : -1;
  const user_tags = item.user_tags
    ? item.user_tags.map((x: string) => get(state, x)).filter(Boolean)
    : [];

  const result = {
    item,
    photos,
    user,
    photo_id,
    user_tags,
    mediaType: media_type,
    nextUrl: undefined,
    prevUrl: undefined,
    shouldPreload: shouldPreload(currentTotal, pos),
    fromResource,
    fromResourceId
  };

  if (-1 < pos) {
    let prefixUrl = '/photo';

    if (photo_set) {
      prefixUrl = `/media/${photo_set}`;
    }

    if (photo_album) {
      prefixUrl = `/media/album/${photo_album}`;
    }

    if (pos < currentTotal - 1) {
      const next = photos[pos + 1];
      const resource_name = next.split('.')[2];
      const slug = get(state, next)?.slug;

      result.nextUrl = `${prefixUrl}/${resource_name}/${next.replace(
        `${resource_name}.entities.${resource_name}.`,
        ''
      )}${slug ? `/${slug}` : ''}`;
    }

    if (0 < pos) {
      const prev = photos[pos - 1];
      const slug = get(state, prev)?.slug;

      const resource_name = prev.split('.')[2];

      result.prevUrl = `${prefixUrl}/${resource_name}/${prev.replace(
        `${resource_name}.entities.${resource_name}.`,
        ''
      )}${slug ? `/${slug}` : ''}`;
    }
  }

  return result;
};

export default connect(mapStateToProps);
