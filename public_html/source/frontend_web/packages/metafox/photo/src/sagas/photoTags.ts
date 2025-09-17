/**
 * @type: saga
 * name: photo.saga.photoTags
 */

import {
  fulfillEntity,
  getGlobalContext,
  getItem,
  LocalAction,
  patchEntity,
  handleActionError
} from '@metafox/framework';
import { concat, without } from 'lodash';
import { takeEvery } from 'redux-saga/effects';
import { ACTION_ADD_TAG, ACTION_REMOVE_TAG } from '@metafox/photo';

export function* onAddPhotoTag(
  action: LocalAction<{ identity: string; data: Record<string, any> }>
) {
  const {
    payload: { identity, data }
  } = action;
  const item = yield* getItem(identity);
  const { apiClient, normalization } = yield* getGlobalContext();

  if (!item) return;

  const sendData = {
    item_id: item.id,
    tag_user_id: data.content?.id,
    px: data.px,
    py: data.py
  };

  try {
    const response = yield apiClient.request({
      url: '/photo-tag',
      method: 'post',
      data: sendData
    });

    const result = yield normalization.normalize(response.data.data);
    const refresh = yield* getItem(identity);

    yield* fulfillEntity(result.data);

    yield* patchEntity(identity, {
      tagged_friends: concat(refresh.tagged_friends ?? [], result.ids),
      total_friends_tagged: item.total_friends_tagged + 1
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* onRemovePhotoTag(
  action: LocalAction<{ identity: string; id: string }>
) {
  const {
    payload: { identity, id }
  } = action;
  const item = yield* getItem(identity);
  const { apiClient } = yield* getGlobalContext();

  if (!item?.tagged_friends) return;

  const _identity = `photo.entities.photo_tag.${id}`;

  yield* patchEntity(identity, {
    tagged_friends: without(item.tagged_friends, _identity),
    total_friends_tagged: item.total_friends_tagged - 1
  });

  yield apiClient.request({
    url: `/photo-tag/${id}`,
    method: 'delete'
  });
}

const sagas = [
  takeEvery(ACTION_REMOVE_TAG, onRemovePhotoTag),
  takeEvery(ACTION_ADD_TAG, onAddPhotoTag)
];

export default sagas;
