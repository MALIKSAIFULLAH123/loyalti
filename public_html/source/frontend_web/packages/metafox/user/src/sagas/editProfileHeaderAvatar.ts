/**
 * @type: saga
 * name: user.saga.editProfileHeaderAvatar
 */

import { updateFeedItem } from '@metafox/feed/sagas/statusComposer';
import {
  fulfillEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  LocalAction,
  patchEntity
} from '@metafox/framework';
import { isFunction } from 'lodash';
import { takeLatest } from 'redux-saga/effects';
import {
  UpdateProfileUserAvatarMeta,
  UpdateProfileUserAvatarProps
} from '../types';

export function* getProfileHeaderAvatar({
  payload: { identity }
}: ItemLocalAction) {
  const { dialogBackend, apiClient, normalization } = yield* getGlobalContext();

  const item = yield* getItem(identity);
  const props: any = { item };

  if (item.avatar_id) {
    const itemImage = yield* getItem(`photo.entities.photo.${item.avatar_id}`);

    const url = `/photo/${item.avatar_id}`;

    if (!itemImage) {
      const response = yield apiClient.request({
        method: 'GET',
        url
      });

      const { image } = response?.data?.data;
      const result = normalization.normalize(response?.data?.data);
      props.avatar = image;

      yield* fulfillEntity(result.data);
    } else {
      props.avatar = itemImage.image;
    }
  }

  yield dialogBackend.present({
    component: 'CropAvatarDialog',
    props
  });
}

export function* updateProfileUserAvatar(
  action: LocalAction<UpdateProfileUserAvatarProps, UpdateProfileUserAvatarMeta>
) {
  const { apiClient, compactUrl } = yield* getGlobalContext();
  const {
    meta: { onFailure, onSuccess },
    payload: { avatar, avatar_crop, identity, image_id }
  } = action;

  const formData = new FormData();

  if (avatar) {
    if (avatar.temp_file) {
      formData.append('temp_file', avatar.temp_file);
    } else {
      formData.append('image', avatar);
    }
  }

  formData.append('image_crop', avatar_crop);

  if (image_id) {
    formData.append('photo_id', image_id.toString());
  }

  try {
    const item = yield* getItem(identity);
    const config = yield* getItemActionConfig(item, 'updateAvatar');

    if (!config?.apiUrl) return;

    const res = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: 'POST',
      data: formData
    });
    const data = res?.data?.data;

    const serverAvatar = data?.user?.avatar;

    const avatar_id = data?.user?.avatar_id;

    yield* patchEntity(identity, {
      avatar: serverAvatar,
      avatar_id
    });

    if (data?.feed_id && !data.isPending) {
      yield* updateFeedItem(data?.feed_id, data.user, true);
    }

    yield* handleActionFeedback(res);

    isFunction(onSuccess) && onSuccess();
  } catch (error) {
    isFunction(onFailure) && onFailure();
    yield* handleActionError(error);
  }
}

export function* updateAvatarFromPhoto({
  payload: { identity }
}: ItemLocalAction) {
  const { apiClient, compactUrl } = yield* getGlobalContext();

  try {
    const item = yield* getItem(identity);
    const config = yield* getItemActionConfig(item, 'makeAvatar');

    if (!config?.apiUrl) return;

    const res = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: 'PUT',
      data: { id: item.id }
    });

    yield* patchEntity(item.user, {
      avatar: item.image,
      avatar_id: item.id
    });
    yield* handleActionFeedback(res);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeLatest('editProfileHeaderAvatar', getProfileHeaderAvatar),
  takeLatest('updateProfileUserAvatar', updateProfileUserAvatar),
  takeLatest('updateAvatarFromPhoto', updateAvatarFromPhoto)
];

export default sagas;
