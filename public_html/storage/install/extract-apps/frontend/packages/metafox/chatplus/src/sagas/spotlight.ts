/**
 * @type: saga
 * name: chatplus.spotlight
 */

import { getGlobalContext, LocalAction } from '@metafox/framework';
import { takeEvery, put, select, debounce } from 'redux-saga/effects';
import { THROTTLE_SEARCH } from '../constants';
import { getFriends, getGroupChatsSelector } from '../selectors';
import { isFunction } from 'lodash';

function* getSpotlightUserInit() {
  const friends = yield select(getFriends);

  return Object.values(friends) ? Object.values(friends)?.slice(0, 4) : [];
}

function* spotlight(
  action: LocalAction<{
    query: string;
    excludes: string;
    users: boolean;
    rooms: boolean;
    checkSortFavorite?: boolean;
  }> & {
    meta: {
      onSuccess: (value: Record<string, any>) => void;
    };
  }
) {
  const { chatplus } = yield* getGlobalContext();
  const {
    query,
    excludes = [],
    users = true,
    rooms = true,
    checkSortFavorite = true
  } = action.payload;

  try {
    yield put({
      type: 'chatplus/spotlight/search/FULFILL',
      payload: { loading: true }
    });

    const result = yield chatplus.waitDdpMethod({
      name: 'chatplus/spotlight',
      params: [query, excludes, { rooms, users }]
    });

    if (!result) return;

    yield put({
      type: 'chatplus/spotlight/search/FULFILL',
      payload: { ...result, loading: false }
    });

    const values = yield select(
      getGroupChatsSelector,
      query,
      checkSortFavorite
    );

    action?.meta?.onSuccess && action.meta.onSuccess(values);
  } catch (error) {}
}

function* spotlightUser(
  action: LocalAction<{
    query: string;
    excludes?: string;
  }> & {
    meta: {
      onSuccess: (values: Record<string, any>[]) => void;
    };
  }
) {
  const { chatplus } = yield* getGlobalContext();
  const { query, excludes = [] } = action.payload;
  const { onSuccess } = action.meta || {};
  const listFriend = yield* getSpotlightUserInit();

  try {
    if (query === '') {
      isFunction(onSuccess) && onSuccess(listFriend || []);

      return;
    }

    const result = yield chatplus.waitDdpMethod({
      name: 'chatplus/spotlight',
      params: [query, excludes, { users: true, room: false }]
    });

    isFunction(onSuccess) && onSuccess(result?.users || []);
  } catch (error) {
    isFunction(onSuccess) && onSuccess(listFriend || []);
  }
}

function* reset(action: LocalAction) {
  const listFriend = yield* getSpotlightUserInit();

  yield put({
    type: 'chatplus/spotlight/search/FULFILL',
    payload: { users: listFriend, searchText: '', rooms: [] }
  });
}

const sagas = [
  debounce(THROTTLE_SEARCH, 'chatplus/spotlight', spotlight),
  debounce(THROTTLE_SEARCH, 'chatplus/spotlight/user', spotlightUser),
  takeEvery('chatplus/spotlight/reset', reset)
];

export default sagas;
