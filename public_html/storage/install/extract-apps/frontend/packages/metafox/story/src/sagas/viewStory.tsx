/**
 * @type: saga
 * name: story.saga.viewStory
 */

import {
  getGlobalContext,
  getResourceAction,
  LocalAction,
  PAGINATION
} from '@metafox/framework';
import { compactUrl } from '@metafox/utils';
import { put, takeLatest } from 'redux-saga/effects';
import { APP_STORY } from '../constants';

export function* loadStoryUser(action: LocalAction<{ user_id: string }>) {
  const {
    payload: { user_id }
  } = action;
  const { getPageParams } = yield* getGlobalContext();
  const pageParam: any = getPageParams();

  const config = yield* getResourceAction(APP_STORY, APP_STORY, 'viewAll');

  if (!config) return;

  yield put({
    type: PAGINATION,
    payload: {
      apiUrl: compactUrl(config.apiUrl, { user_id }),
      pagingId: `${pageParam?.module_name}/user_story/${user_id}`
    }
  });
}

const sagas = [takeLatest('story/user_story/LOAD', loadStoryUser)];

export default sagas;
