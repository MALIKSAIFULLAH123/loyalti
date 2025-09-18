/**
 * @type: saga
 * name: forum_thread.saga.copyItem
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* copyItem({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { compactUrl, dialogBackend } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'copyItem');

  if (!config?.apiUrl) return;

  try {
    yield dialogBackend.present({
      component: 'forum.dialog.FormDialog',
      props: {
        formUrl: compactUrl(config.apiUrl, item)
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('forum/copyItem', copyItem)];

export default sagas;
