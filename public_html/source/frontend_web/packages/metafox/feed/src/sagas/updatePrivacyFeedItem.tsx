/**
 * @type: saga
 * name: core.updatePrivacyFeedItem
 */
import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { updateFeedItem } from './statusComposer';

function* updatePrivacyFeedItem(action: ItemLocalAction) {
  const { apiClient, compactUrl, dialogBackend } = yield* getGlobalContext();
  const { identity, privacy } = action.payload;
  const item = yield* getItem(identity);
  const parentUser = yield* getItem(item?.parent_user);
  const config = yield* getItemActionConfig(item, 'updatePrivacy');

  if (!config?.apiUrl) {
    return yield dialogBackend.alert({
      title: 'Error',
      message: 'Oops!.'
    });
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const dataItem = { privacy };

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod,
      url: compactUrl(config?.apiUrl, item),
      data: dataItem
    });

    yield* updateFeedItem(item.id, parentUser, false);
    yield* handleActionFeedback(response);
  } catch (err) {
    yield* handleActionError(err);
  }
}

const sagas = [takeEvery('updatePrivacyFeedItem', updatePrivacyFeedItem)];

export default sagas;
