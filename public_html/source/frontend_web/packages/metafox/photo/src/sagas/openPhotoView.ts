/**
 * @type: saga
 * name: photo.saga.openPhotoView
 */

import {
  getGlobalContext,
  getResourceAction,
  LocalAction,
  PAGINATION_REFRESH,
  patchEntity,
  getItem,
  fulfillEntity
} from '@metafox/framework';
import { compactUrl, compactData } from '@metafox/utils';
import { put, takeLatest } from 'redux-saga/effects';
import {
  APP_PHOTO,
  RESOURCE_ALBUM,
  PHOTO_PAGINATION,
  RESOURCE_PHOTO_SET
} from '../constant';

type OpenPhotoViewAction = LocalAction<{ id: string }>;

function* openPhotoView(action: OpenPhotoViewAction) {
  const {
    payload: { id }
  } = action;
  const { navigate } = yield* getGlobalContext();
  const pathname = `photo/${id}`;

  navigate({ pathname }, { state: { asModal: true } });
}

export function* loadPhotoSetSuccess(
  action: LocalAction<
    { identity: string; direction?: 'next' | 'prev' },
    { ids: string[]; pagingId: string }
  >
) {
  const {
    payload,
    meta: { ids, pagesOffset }
  } = action;
  const { normalization } = yield* getGlobalContext();
  const { identity } = payload || {};
  const item = yield* getItem(identity);

  if (item) {
    yield* patchEntity(identity, {
      photos: ids,
      statistic: pagesOffset.total
        ? { ...item.statistic, total_item: pagesOffset.total }
        : item.statistic
    });
  } else {
    const result = normalization.normalize([
      {
        module_name: 'photo',
        resource_name: 'photo_set',
        photos: ids,
        id: identity.split('.')[3]
      }
    ]);
    yield* fulfillEntity(result.data);
  }
}

export function* loadPhotosSuccess(
  action: LocalAction<{ identity: string }, { ids: string[]; pagingId: string }>
) {
  const {
    payload: { identity },
    meta: { ids }
  } = action;

  yield* patchEntity(identity, { photos: ids });
}

export function* loadPhotoSet(
  action: LocalAction<
    {
      photo_set: string;
      direction?: 'next' | 'prev';
      media_id?: number;
    },
    {
      onSuccess?: () => void;
      onError?: () => void;
    }
  >
) {
  const { payload, meta } = action;
  const { photo_set, direction, media_id } = payload || {};
  const { onSuccess, onError } = meta || {};

  const identity = `photo.entities.photo_set.${photo_set}`;
  const config = yield* getResourceAction(
    APP_PHOTO,
    RESOURCE_PHOTO_SET,
    'viewDetailMediaItem'
  );

  if (!config) return;

  yield put({
    type: PHOTO_PAGINATION,
    payload: {
      apiUrl: compactUrl(config.apiUrl, { id: photo_set }),
      apiParams: compactData(config.apiParams, { media_id }),
      pagingId: `photo-set/next-prev/${photo_set}`,
      direction
    },
    meta: {
      onSuccess,
      onError,
      successAction: {
        type: 'photo/photo_set/LOAD_SUCCESS',
        payload: { identity, photo_set }
      }
    }
  });
}

export function* loadPhotoAlbum(
  action: LocalAction<
    {
      photo_album: string;
      direction?: 'next' | 'prev';
      media_id?: number;
    },
    {
      onSuccess?: () => void;
      onError?: () => void;
    }
  >
) {
  const { payload, meta } = action;
  const { photo_album, direction, media_id } = payload || {};
  const { onSuccess, onError } = meta || {};
  const identity = `photo.entities.photo_album.${photo_album}`;
  const config = yield* getResourceAction(
    APP_PHOTO,
    RESOURCE_ALBUM,
    'viewDetailMediaItem'
  );

  if (!config) return;

  yield put({
    type: PHOTO_PAGINATION,
    payload: {
      apiUrl: compactUrl(config.apiUrl, { id: photo_album }),
      apiParams: compactData(config.apiParams, { media_id }),
      pagingId: `photo-album/next-prev/${photo_album}`,
      direction
    },
    meta: {
      successAction: {
        type: 'photo/photos/LOAD_SUCCESS',
        payload: { identity, photo_album }
      },
      onSuccess,
      onError
    }
  });
}
export function* reLoadPhotoAlbum(
  action: LocalAction<{ photo_album: string }>
) {
  const {
    payload: { photo_album }
  } = action;
  const config = yield* getResourceAction(
    APP_PHOTO,
    RESOURCE_ALBUM,
    'getAlbumItems'
  );

  if (!config) return;

  yield put({
    type: PAGINATION_REFRESH,
    payload: {
      apiUrl: compactUrl(config.apiUrl, { id: photo_album }),
      pagingId: `photo-album/${photo_album}`
    }
  });
}

export function* presentSimplePhoto({ payload }: LocalAction<{ src: string }>) {
  const { dialogBackend } = yield* getGlobalContext();

  yield dialogBackend.present({
    component: 'photo.dialog.simplePhoto',
    props: payload
  });
}

export function* presentCreateNewAlbum({
  payload
}: LocalAction<{ id: string }>) {
  const { dialogBackend } = yield* getGlobalContext();

  yield dialogBackend.present({
    component: 'photo.dialog.addPhotoAlbum',
    props: payload
  });
}

const sagas = [
  takeLatest('photo/openPhotoView', openPhotoView),
  takeLatest('photo/photo_set/LOAD', loadPhotoSet),
  takeLatest('photo/photo_album/LOAD', loadPhotoAlbum),
  takeLatest('photo/photo_album/RELOAD', reLoadPhotoAlbum),
  takeLatest('photo/photos/LOAD_SUCCESS', loadPhotosSuccess),
  takeLatest('photo/photo_set/LOAD_SUCCESS', loadPhotoSetSuccess),
  takeLatest('photo/presentSimplePhoto', presentSimplePhoto),
  takeLatest('photo/presentCreateNewAlbum', presentCreateNewAlbum)
];

export default sagas;
