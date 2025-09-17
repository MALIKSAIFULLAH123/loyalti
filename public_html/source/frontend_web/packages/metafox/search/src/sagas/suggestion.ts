/**
 * @type: saga
 * name: search.suggestions
 */

import { getGlobalContext, GlobalState } from '@metafox/framework';
import { APP_USER } from '@metafox/user/constant';
import { put, select, debounce } from 'redux-saga/effects';

const APP_GROUP = 'group';
const APP_PAGE = 'page';
const APP_FRIEND = 'friend';

type QueryAction = {
  type: string;
  payload: {
    text: string;
  };
};

const selectSuggestion = (state: GlobalState, text: string) => {
  return state.search.suggestions[text];
};

export function* query(action: QueryAction) {
  const { text } = action.payload;

  if (!text) return;

  try {
    const prev = yield select(selectSuggestion, text);

    if (prev?.loaded) return;

    const { apiClient, i18n } = yield* getGlobalContext();

    const response = yield apiClient.request({
      method: 'get',
      url: '/search/suggestion',
      params: { q: text, limit: 10 }
    });

    const result = response.data?.data;

    const data: { title: string; image: string; to: string } = result
      .map((item: any) => {
        switch (item.resource_name) {
          case APP_GROUP:
            return {
              title: item.title,
              image: item.cover,
              to: `/${APP_GROUP}/${item.id}`,
              note: i18n.formatMessage({
                id: `name_type_${item.resource_name}`
              })
            };
          case APP_PAGE:
            return {
              title: item.title,
              image: item.avatar,
              to: `/${APP_PAGE}/${item.id}`,
              note: i18n.formatMessage({
                id: `name_type_${item.resource_name}`
              })
            };
          case APP_USER:
            return {
              title: item.full_name,
              image: item.avatar,
              to: `/${item.user_name}`,
              note: i18n.formatMessage({
                id: `name_type_${item.resource_name}`
              })
            };
          case APP_FRIEND:
            return {
              title: item.full_name,
              image: item.avatar,
              to: `/${item.user_name}`,
              note: i18n.formatMessage({
                id: `name_type_${item.resource_name}`
              })
            };
          default:
            return false;
        }
      })
      .filter(Boolean);

    yield put({ type: 'suggestions/FULFILL', payload: { text, data } });
  } catch (err) {
    console.log(err);
    // handle error
  }
}

const sagas = [debounce(500, 'suggestions/QUERY', query)];

export default sagas;
