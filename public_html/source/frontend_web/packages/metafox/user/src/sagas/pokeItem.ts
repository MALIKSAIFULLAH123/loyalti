/**
 * @type: saga
 * name: user.saga.friendRequest
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

export function* pokeItem(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();

  if (!item) return null;

  const config = yield* getItemActionConfig(item, 'pokeItem');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod || 'post',
      url: compactUrl(config.apiUrl, item),
      data: compactData({ user_id: ':id' }, item)
    });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagaEffect = takeEvery('pokeItem', pokeItem);

export default sagaEffect;
