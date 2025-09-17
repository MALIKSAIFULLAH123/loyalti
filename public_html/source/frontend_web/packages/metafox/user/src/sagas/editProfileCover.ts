/**
 * @type: saga
 * name: saga.editProfileCover
 */

import { updateFeedItem } from '@metafox/feed/sagas/statusComposer';
import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity,
  makeDirtyPaging
} from '@metafox/framework';
import { isFunction } from 'lodash';
import { takeEvery } from 'redux-saga/effects';
import { UpdateProfileCoverAction } from '../types';

export function* updateProfileCover(action: UpdateProfileCoverAction) {
  const {
    meta: { onFailure, onSuccess },
    payload: { file, identity, position, tempFile }
  } = action;

  try {
    const item = yield* getItem(identity);

    if (!item) return;

    const config = yield* getItemActionConfig(item, 'updateProfileCover');
    const { apiClient, compactUrl } = yield* getGlobalContext();

    if (!config?.apiUrl) return;

    const formData = new FormData();

    if (tempFile) {
      formData.append('temp_file', tempFile?.temp_file);
    } else if (file) {
      formData.append('image', file);
    }

    formData.append('position', position.y.toString());

    const res = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: 'post',
      data: formData
    });

    const data = res?.data?.data;

    if (position) {
      yield* patchEntity(item._identity, {
        cover_photo_position: position.y.toString()
      });
    }

    if (file) {
      yield* patchEntity(item._identity, {
        cover: data?.user?.cover,
        cover_photo_id: data?.user?.cover_photo_id,
        extra: data?.user?.extra
      });
    }

    if (file && data?.feed_id && !data.is_pending) {
      yield* updateFeedItem(data?.feed_id, data.user, true);
    }

    yield* handleActionFeedback(res);
    isFunction(onSuccess) && onSuccess();
  } catch (error) {
    isFunction(onFailure) && onFailure(error);
    yield* handleActionError(error);
  }
}
export function* updateCoverFromListingPhoto({
  payload: { identity },
  meta
}: UpdateProfileCoverAction) {
  const { apiClient, compactUrl } = yield* getGlobalContext();

  try {
    const item = yield* getItem(identity);
    const config = yield* getItemActionConfig(item, 'makeCover');

    if (!config?.apiUrl) return;

    const res = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: config.apiMethod,
      data: { id: item.id }
    });

    yield* patchEntity(item.user, {
      cover: item.image.origin,
      cover_photo_id: item.id
    });

    yield* handleActionFeedback(res);
    isFunction(meta.onSuccess) && meta.onSuccess();
    isFunction(meta.onFailure) && meta.onFailure();
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* updateCoverFromPhoto({
  payload: { photoIdentity, position, identity },
  meta
}: UpdateProfileCoverAction) {
  const { apiClient, compactUrl } = yield* getGlobalContext();

  try {
    const item = yield* getItem(photoIdentity);
    const owner = yield* getItem(identity);

    const config = yield* getItemActionConfig(item, 'makeCover');

    if (!config?.apiUrl) return;

    const res = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: config.apiMethod,
      data: { id: item.id, position: position.y.toString(), user_id: owner.id }
    });

    const data = res?.data?.data;
    const { id } = data || {};

    yield* patchEntity(identity, {
      cover: item.image,
      cover_photo_id: id,
      cover_photo_position: position.y.toString()
    });

    yield* makeDirtyPaging('feed');
    yield* handleActionFeedback(res);
    isFunction(meta.onSuccess) && meta.onSuccess();
    isFunction(meta.onFailure) && meta.onFailure();
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* removeCoverPhoto({
  payload: { identity },
  meta
}: ItemLocalAction<any, any>) {
  const { apiClient, compactUrl } = yield* getGlobalContext();
  const item = yield* getItem(identity);

  const config = yield* getItemActionConfig(item, 'removeProfileCover');
  const ok = yield* handleActionConfirm(config);

  if (!ok) {
    return;
  }

  try {
    const res = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: config.apiMethod
    });

    yield* patchEntity(identity, {
      cover: null,
      cover_photo_id: null,
      cover_photo_position: null
    });

    yield* handleActionFeedback(res);
    isFunction(meta.onSuccess) && meta.onSuccess();
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* chooseCoverPhotoDialog({
  payload: { identity },
  meta
}: ItemLocalAction<any, any>) {
  const { dialogBackend } = yield* getGlobalContext();

  dialogBackend.present({
    component: 'photo.dialog.chooseCoverPhoto',
    props: {
      identity,
      onSuccess: meta?.onSuccess,
      onFailure: meta?.onFailure
    }
  });
}

export function* chooseAvatarPhotoDialog({
  payload: { identity },
  meta
}: ItemLocalAction<any, any>) {
  const { dialogBackend } = yield* getGlobalContext();

  dialogBackend.present({
    component: 'photo.dialog.chooseAvatarPhoto',
    props: {
      identity,
      onSuccess: meta?.onSuccess,
      onFailure: meta?.onFailure
    }
  });
}

export function* updateParentCoverFromPhoto({
  payload: { identity },
  meta
}: UpdateProfileCoverAction) {
  const { apiClient, compactUrl } = yield* getGlobalContext();

  try {
    const item = yield* getItem(identity);
    const config = yield* getItemActionConfig(item, 'makeParentCover');

    if (!config?.apiUrl) return;

    const res = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: config.apiMethod,
      data: { id: item.id }
    });

    yield* patchEntity(item.owner, {
      cover: item.image.origin,
      cover_photo_id: item.id
    });

    yield* handleActionFeedback(res);
    isFunction(meta.onSuccess) && meta.onSuccess();
    isFunction(meta.onFailure) && meta.onFailure();
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* updateParentAvatarPhoto({
  payload: { identity },
  meta
}: UpdateProfileCoverAction) {
  const { apiClient, compactUrl } = yield* getGlobalContext();

  try {
    const item = yield* getItem(identity);
    const config = yield* getItemActionConfig(item, 'makeParentAvatar');

    if (!config?.apiUrl) return;

    const res = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: config.apiMethod,
      data: { id: item.id }
    });

    yield* patchEntity(item.owner, {
      avatar: item.image,
      avatar_id: item.id
    });

    yield* handleActionFeedback(res);
    isFunction(meta.onSuccess) && meta.onSuccess();
    isFunction(meta.onFailure) && meta.onFailure();
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('updateProfileCover', updateProfileCover),
  takeEvery('updateCoverFromPhoto', updateCoverFromPhoto),
  takeEvery('updateCoverFromListingPhoto', updateCoverFromListingPhoto),
  takeEvery('removeCoverPhoto', removeCoverPhoto),
  takeEvery('chooseCoverPhotoDialog', chooseCoverPhotoDialog),
  takeEvery('chooseAvatarPhotoDialog', chooseAvatarPhotoDialog),
  takeEvery('updateParentCoverFromPhoto', updateParentCoverFromPhoto),
  takeEvery('updateParentAvatarPhoto', updateParentAvatarPhoto)
];

export default sagas;
