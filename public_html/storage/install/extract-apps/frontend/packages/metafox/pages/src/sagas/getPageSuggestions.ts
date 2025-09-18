/**
 * @type: saga
 * name: friend.saga.pageSuggestions
 */
import {
  getGlobalContext,
  getResourceAction,
  handleActionError,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { APP_PAGE, RESOURCE_PAGE } from '../constant';

export function* getSuggestions({
  payload,
  meta
}: ItemLocalAction<{ q: string }, { onSuccess: (value) => void }>) {
  try {
    const { q: query } = payload;
    const { onSuccess } = meta;
    const { apiClient } = yield* getGlobalContext();
    const remoteDataSource = yield* getResourceAction(
      APP_PAGE,
      RESOURCE_PAGE,
      'viewAll'
    );
    const apiUrl = remoteDataSource?.apiUrl;

    if (!apiUrl) return;

    const response = yield apiClient.request({
      url: apiUrl,
      method: 'get',
      params: { q: query || undefined, limit: 10 }
    });

    const result = response.data.data;

    onSuccess && onSuccess(result);
  } catch (err) {
    yield* handleActionError(err);
  }
}

const sagaEffect = [takeEvery('page/getSuggestions', getSuggestions)];

export default sagaEffect;
