/**
 * @type: saga
 * name: friend.saga.friendSuggestions
 */
import { getGlobalContext, ItemLocalAction } from '@metafox/framework';
import { debounce } from 'redux-saga/effects';

const cached = {};

export function* getSuggestions({
  payload,
  meta
}: ItemLocalAction<
  {
    q: string;
    apiUrl: string;
    none: string;
    limit: number;
    initialParams: Record<string, any>;
  },
  { onSuccess: (data: unknown) => void }
>) {
  const { q, none, apiUrl: url, limit = 10, initialParams } = payload;
  const { onSuccess } = meta;

  try {
    const { apiClient } = yield* getGlobalContext();
    const key = none + (q ?? '') + (JSON.stringify(initialParams) ?? '');

    if (cached[key]) {
      yield onSuccess({
        loading: false,
        items: cached[key]
      });

      return;
    }

    yield onSuccess({
      loading: true,
      items: []
    });

    const response = yield apiClient.request({
      url,
      method: 'get',
      params: { q: q || undefined, limit, ...initialParams }
    });

    const items = Array.isArray(response.data.data) ? response.data.data : [];

    cached[key] = items;

    onSuccess &&
      onSuccess({
        loading: false,
        items
      });
  } catch (err) {
    // console.log(err);
  }
}

const sagaEffect = [debounce(300, '@suggestion', getSuggestions)];

export default sagaEffect;
