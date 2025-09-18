/**
 * @type: saga
 * name: story.saga.muteStory
 */

import {
  deleteEntity,
  getGlobalContext,
  getItem,
  getResourceAction,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  LocalAction,
  patchEntity
} from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';
import { APP_STORY, RESOURCE_STORY_MUTE } from '../constants';
import { compactData } from '@metafox/utils';
import { isFunction } from 'lodash';

export function* muteStory(
  action: LocalAction<{ identity: string }, { onSuccess: (data: any) => void }>
) {
  const { dialogBackend, dispatch } = yield* getGlobalContext();

  const dataSource = yield* getResourceAction(
    APP_STORY,
    RESOURCE_STORY_MUTE,
    'mute'
  );

  const { identity } = action.payload || {};
  const { onSuccess } = action.meta || {};

  const story = yield* getItem(identity);
  const user = yield* getItem(story?.user);

  if (!dataSource) return;

  yield dialogBackend
    .present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        maxWidth: 'xs',
        pageParams: { user_id: user?.id }
      }
    })
    .then(response => {
      if (!response?.id) return;

      dispatch({
        type: 'story/updateItemMuted',
        payload: { ...response, identity }
      });
      isFunction(onSuccess) && onSuccess(response);
    });
}

export function* unmuteStory(action: LocalAction<{ identity: string }>) {
  const { apiClient } = yield* getGlobalContext();

  const dataSource = yield* getResourceAction(
    APP_STORY,
    RESOURCE_STORY_MUTE,
    'unmute'
  );

  const { identity } = action.payload || {};

  const story = yield* getItem(identity);
  const user = yield* getItem(story?.user);

  if (!dataSource) return;

  try {
    yield* patchEntity(identity, {
      is_muted: false
    });

    const response = yield apiClient.request({
      method: dataSource?.apiMethod || 'PATCH',
      url: dataSource.apiUrl,
      data: compactData(dataSource.apiParams, { user_id: user?.id })
    });

    const identityMuted = `story.entities.story_mute.${user?.id}`;

    yield* handleActionFeedback(response);
    yield* deleteEntity(identityMuted);

    return true;
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* openMutedDialog(action: LocalAction) {
  const { dialogBackend } = yield* getGlobalContext();

  yield dialogBackend.present({
    component: 'story.dialog.dialogMutedListing'
  });
}

export function* updateItemMuted(action: ItemLocalAction & { payload: any }) {
  const { id, identity } = action.payload || {};
  const identityUser = `story.entities.user_story.${id}`;
  const user = yield* getItem(identityUser);

  try {
    yield* patchEntity(identity, {
      is_muted: true
    });

    if (!user) return;

    yield* deleteEntity(identityUser);
  } catch (error) {}
}

const sagas = [
  takeLatest('story/mute', muteStory),
  takeLatest('story/unmute', unmuteStory),
  takeLatest('story/openMutedDialog', openMutedDialog),
  takeLatest('story/updateItemMuted', updateItemMuted)
];

export default sagas;
