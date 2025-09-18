/**
 * @type: saga
 * name: saga.group.search
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
import { APP_GROUP } from '../constant';

export function* searchInGroup(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);

  const { dialogBackend, i18n } = yield* getGlobalContext();

  const formSchema = yield select(
    getResourceForm,
    APP_GROUP,
    APP_GROUP,
    'search_in'
  );

  const dataSource = yield* getResourceAction(
    APP_GROUP,
    APP_GROUP,
    'viewSearchInGroup'
  ) as any;

  const apiRules = dataSource?.apiRules;

  const params = whenParamRules(formSchema?.value, apiRules);

  dialogBackend.present({
    component: 'core.dialog.searchInModule',
    props: {
      title: i18n.formatMessage({ id: 'search_this_group' }),
      placeholder: i18n.formatMessage({
        id: 'search_posts_photos_videos_and_more'
      }),
      item,
      params
    }
  });
}

const sagas = [takeEvery('group/search', searchInGroup)];

export default sagas;
