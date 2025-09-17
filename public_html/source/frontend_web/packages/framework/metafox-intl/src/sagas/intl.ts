/**
 * @type: saga
 * name: metafox.intl
 */

import {
  APP_BOOTSTRAP,
  getGlobalContext,
  GlobalState,
  LOCALE_GROUP,
  IS_ADMINCP,
  USE_BOOTSTRAP_CACHE as CACHED
} from '@metafox/framework';
import { get } from 'lodash';
import { put, select, takeLeading } from 'redux-saga/effects';

const CACHED_PHRASE_KEY = IS_ADMINCP ? 'phrases.admin' : 'phrases';

export function* bootstrapIntl() {
  // load locale data/

  const { apiClient, localStore, preferenceBackend } =
    yield* getGlobalContext();

  try {
    let data: Record<string, string> = CACHED
      ? localStore.getJSON(CACHED_PHRASE_KEY)
      : undefined;

    const revision = CACHED ? get(data, 'revision', 'now') : 'now';

    const defaultMessages = yield select(
      (state: GlobalState) => state.intl.messages
    );

    const response = yield apiClient.get(
      `core/translation/${LOCALE_GROUP}/auto/${revision}`
    );

    const keepCached = get(response, 'data.data.keepCached', false);
    const $locale = get(response, 'data.data.$locale');

    if ($locale) {
      preferenceBackend.set('userLanguage', $locale);
      // handle locale changed.
    }

    if (!keepCached) {
      data = response.data.data;
      localStore.set(CACHED_PHRASE_KEY, JSON.stringify(data));
    }

    yield put({
      type: 'intl/setMessages',
      payload: { ...defaultMessages, ...data }
    });
    yield put({ type: '@bootstrap/intl/DONE' });
  } catch (err) {
    yield put({ type: '@bootstrap/intl/DONE', error: err });
  }
}

const sagas = [takeLeading(APP_BOOTSTRAP, bootstrapIntl)];

export default sagas;
