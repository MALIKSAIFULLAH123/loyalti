/**
 * @type: saga
 * name: story.saga.updateStory
 */

import {
  ItemLocalAction,
  deleteEntity,
  fulfillEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  getResourceAction,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  patchEntity
} from '@metafox/framework';
import { takeLatest, put } from 'redux-saga/effects';
import {
  APP_FRIEND,
  APP_STORY,
  RESOURCE_FRIEND_SUGGESTION,
  RESOURCE_STORY_REACTION,
  RESOURCE_STORY_VIEW
} from '../constants';
import { StoryItemProps } from '../types';
import { difference, isFunction } from 'lodash';

function* updateView(action: {
  type: 'story/updateView';
  payload: {
    story: StoryItemProps;
    lastStory?: boolean;
    identityUser: string;
  };
}) {
  const { apiClient, compactData } = yield* getGlobalContext();
  const { story, lastStory = false, identityUser } = action.payload || {};

  const user = yield* getItem(identityUser);

  if (!story) return;

  try {
    const config = yield* getResourceAction(
      APP_STORY,
      RESOURCE_STORY_VIEW,
      'addItem'
    );

    const response = yield apiClient.request({
      method: config?.apiMethod || 'POST',
      url: config.apiUrl,
      data: compactData(config.apiParams, { story_id: story?.id })
    });

    const data = response.data?.data;

    if (data.has_seen && story?._identity) {
      yield* patchEntity(story?._identity, {
        has_seen: true
      });
    }

    if (lastStory && (identityUser || user?._identity)) {
      yield* patchEntity(identityUser || user?._identity, {
        can_view_story: true,
        has_new_story: false
      });

      yield* patchEntity(
        `user.entities.user.${identityUser || user?._identity}`,
        {
          can_view_story: true,
          has_new_story: false
        }
      );
    }
  } catch (error) {}
}

function* sendReaction(action: ItemLocalAction & { payload: any }) {
  const { apiClient, compactData, normalization } = yield* getGlobalContext();
  const { identity, reaction_id } = action.payload || {};
  const { onSuccess } = action.meta || {};

  const story = yield getItem(identity);

  if (!identity || !story) return;

  try {
    const config = yield* getResourceAction(
      APP_STORY,
      RESOURCE_STORY_REACTION,
      'addItem'
    );

    const response = yield apiClient.request({
      method: config?.apiMethod || 'POST',
      url: config.apiUrl,
      data: compactData(config.apiParams, { story_id: story?.id, reaction_id })
    });

    const data = response.data?.data;

    if (data) {
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }

    typeof onSuccess === 'function' && onSuccess();
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* deleteStory(
  action: ItemLocalAction & {
    payload: any;
    meta: { onSuccess?: () => void; onFailure?: () => void };
  }
) {
  const { identity } = action.payload || {};
  const { onSuccess } = action?.meta || {};
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const { module_name, resource_name } = item;

  const config = yield* getItemActionConfig(item, 'deleteItem');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return false;

  try {
    yield* deleteEntity(identity);

    yield put({
      type: `${module_name}/${resource_name}/deleteItem/DONE`,
      payload: { ...action.payload, ...item }
    });

    if (isFunction(onSuccess)) onSuccess();

    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const response = yield apiClient.request({
      method: config?.apiMethod || 'delete',
      url: compactUrl(config.apiUrl, item)
    });

    return true;
  } catch (error) {
    yield* handleActionError(error);
  }

  return false;
}

function* deleteItemDone(action: ItemLocalAction & { payload: any }) {
  const { user: identityPayload, identity } = action.payload;

  const userSession = yield* getItem(identityPayload);

  const identityUser = `story.entities.user_story.${userSession?.id}`;

  const user = yield* getItem(identityUser);

  if (!user || !identity) return;

  try {
    if (user?.stories.includes(identity)) {
      let stories = [...user?.stories];

      stories = difference(stories, [identity]);

      if (stories.length === 0) {
        yield* deleteEntity(identityUser);

        yield* patchEntity(`user.entities.user.${userSession?.id}`, {
          can_view_story: false,
          has_new_story: false
        });

        return;
      }

      yield* patchEntity(identityUser, {
        stories
      });
    }
  } catch (error) {}
}

function* hideUserSuggest(action: ItemLocalAction & { payload: any }) {
  const { apiClient } = yield* getGlobalContext();
  const { user_id } = action.payload || {};

  if (!user_id) return;

  try {
    const config = yield* getResourceAction(
      APP_FRIEND,
      RESOURCE_FRIEND_SUGGESTION,
      'hideUserSuggestion'
    );

    const response = yield apiClient.request({
      method: config?.apiMethod || 'POST',
      url: config?.apiUrl,
      data: { user_id }
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeLatest('story/updateView', updateView),
  takeLatest('story/sendReaction', sendReaction),
  takeLatest('story/deleteItem', deleteStory),
  takeLatest('story/story/deleteItem/DONE', deleteItemDone),
  takeLatest('story/sugestion/hideUser', hideUserSuggest)
];

export default sagas;
