/**
 * @type: saga
 * name: core.copyLink
 */

import { getGlobalContext, getItem, ItemLocalAction } from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';

function* copyLink(action: ItemLocalAction) {
  const { copyToClipboard, toastBackend, i18n } = yield* getGlobalContext();
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);

  if (!item?.url) return;

  yield copyToClipboard(item.url);
  toastBackend.success(i18n.formatMessage({ id: 'copied_to_clipboard' }));
}

const sagas = [takeLatest('copyLink', copyLink)];

export default sagas;
