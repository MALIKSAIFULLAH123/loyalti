/**
 * @type: saga
 * name: friend.saga.suggestions
 */

import {
  deleteEntity,
  getGlobalContext,
  getItem,
  getResourceAction,
  GlobalState,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction
} from '@metafox/framework';
import { getImageSrc } from '@metafox/utils';
import { debounce, put, select, takeEvery } from 'redux-saga/effects';
import { APP_FRIEND, RESOURCE_FRIEND_SUGGESTION } from '../constant';
import { isEmpty } from 'lodash';

type QueryAction = {
  type: string;
  payload: {
    text: string;
    item_id: number;
    item_type: string;
    excludeIds?: Array<number>;
    is_full?: boolean;
    user_id?: any;
    owner_id?: any;l
  };
};

const selectSuggestion = (state: GlobalState, text: string) => {
  return state.friend.suggestions[text];
};

export function* query(action: QueryAction) {
  const {
    text = '',
    item_id = null,
    item_type = null,
    excludeIds,
    is_full,
    user_id,
    owner_id
  } = action.payload;
  const prev = yield select(selectSuggestion, text);

  if (prev?.loaded) return;

  const { apiClient } = yield* getGlobalContext();
  const response = yield apiClient.request({
    method: 'get',
    url: '/friend/tag-suggestion',
    params: {
      q: text || undefined,
      limit: 3,
      item_id,
      item_type,
      excluded_ids: excludeIds,
      is_full,
      user_id: user_id || undefined,
      owner_id: owner_id || undefined
    }
  });

  const result = response.data?.data;

  const data = Array.isArray(result)
    ? result.map(item => ({
        id: item.id,
        image: getImageSrc(item.avatar, '240'),
        label: item.full_name,
        resource_name: item.resource_name,
        module_name: item.module_name
      }))
    : [];

  yield put({ type: 'friend/suggestions/FULFILL', payload: { text, data } });
}

function* hideUserSuggestion(action: ItemLocalAction & { payload: any }) {
  const { apiClient } = yield* getGlobalContext();
  const { identity } = action.payload || {};

  const user = yield* getItem(identity);

  if (isEmpty(user)) return;

  try {
    const config = yield* getResourceAction(
      APP_FRIEND,
      RESOURCE_FRIEND_SUGGESTION,
      'hideUserSuggestion'
    );

    const response = yield apiClient.request({
      method: config?.apiMethod || 'POST',
      url: config?.apiUrl,
      data: { user_id: user?.id }
    });

    if (response?.data) {
      const status = response.data?.status;

      if (status === 'success') {
        yield* deleteEntity(identity);
      }
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* showReportDialog(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return null;

  const { dialogBackend } = yield* getGlobalContext();

  yield dialogBackend.present({
    component: 'report.dialog.addReport',
    props: {
      item_id: item.id,
      item_type: 'user'
    }
  });
}

const sagas = [
  debounce(500, 'friend/suggestions/LOAD', query),
  takeEvery('friend/hideUserSuggestion', hideUserSuggestion),
  takeEvery('friend_suggestion/reportItem', showReportDialog)
];

export default sagas;
