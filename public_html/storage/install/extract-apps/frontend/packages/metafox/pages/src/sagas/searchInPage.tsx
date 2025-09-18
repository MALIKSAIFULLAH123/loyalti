/**
 * @type: saga
 * name: saga.page.searchInPage
 */

import {
  getGlobalContext,
  getItem,
  getResourceAction,
  getResourceForm,
  ItemLocalAction
} from '@metafox/framework';
import { whenParamRules } from '@metafox/utils';
import { takeEvery, select } from 'redux-saga/effects';
import { APP_PAGE } from '../constant';

export function* searchInPage(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);

  const { dialogBackend, i18n } = yield* getGlobalContext();

  const formSchema = yield select(
    getResourceForm,
    APP_PAGE,
    APP_PAGE,
    'search_in'
  );

  const dataSource = yield* getResourceAction(
    APP_PAGE,
    APP_PAGE,
    'viewSearchInPage'
  ) as any;

  const apiRules = dataSource?.apiRules;

  const params = whenParamRules(formSchema?.value, apiRules);

  dialogBackend.present({
    component: 'core.dialog.searchInModule',
    props: {
      title: i18n.formatMessage({ id: 'search_this_page' }),
      placeholder: i18n.formatMessage({
        id: 'search_posts_photos_videos_and_more'
      }),
      item,
      params
    }
  });
}

const sagas = [takeEvery('page/search', searchInPage)];

export default sagas;
