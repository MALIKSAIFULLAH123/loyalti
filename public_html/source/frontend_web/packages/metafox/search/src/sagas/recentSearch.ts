/**
 * @type: saga
 * name: search.recentSearch
 */
import { getGlobalContext } from '@metafox/framework';
import { isString, uniq } from 'lodash';
import { put, takeEvery, takeLatest } from 'redux-saga/effects';

const STORE_KEY = 'recentSearch';

export function* load() {
  const { localStore } = yield* getGlobalContext();
  const data = localStore.getJSON(STORE_KEY);

  return Array.isArray(data) ? data : [];
}

export function* update(data: string[]) {
  const { localStore } = yield* getGlobalContext();
  localStore.set(STORE_KEY, JSON.stringify(data));

  yield put({
    type: 'recentSearch/FULFILL',
    payload: {
      data: Array.isArray(data) ? data : [],
      loaded: true
    }
  });
}

export function* init() {
  const data = yield* load();
  yield* update(data);
}

export function* remove(action: { type: string; payload: { text: string } }) {
  const { text } = action.payload;

  if (!isString(text)) return;

  const data = yield* load();
  const newData = data.filter(x => x !== text);
  yield* update(newData);
}

export function* clear(action: { type: string; payload: unknown }) {
  yield* update([]);
}

export function* add(action: { type: string; payload: { text: string } }) {
  const { text } = action.payload;

  if (!isString(text)) return;

  const data = yield* load();

  data.unshift(text);
  const newData = uniq(data).slice(0, 25);
  yield* update(newData);
}

const sagas = [
  takeLatest('recentSearch/INIT', init),
  takeEvery('recentSearch/REMOVE', remove),
  takeEvery('recentSearch/CLEAR', clear),
  takeEvery('recentSearch/ADD', add)
];

export default sagas;
