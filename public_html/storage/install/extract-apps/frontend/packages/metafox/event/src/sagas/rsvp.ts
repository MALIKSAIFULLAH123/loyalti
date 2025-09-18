/**
 * @type:saga
 * name: event/rsvp
 */

import {
  deleteEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity,
  fulfillEntity,
  getSession
} from '@metafox/framework';
import { compactData } from '@metafox/utils';
import { takeLatest, put } from 'redux-saga/effects';
import { NOT_INTERESTED, ON_GOING } from '../constants';

function* going(
  action: ItemLocalAction & { payload: { keepEntity: boolean } }
) {
  const { identity, keepEntity } = action.payload;
  const item = yield getItem(identity);

  if (!item) return;

  const { apiClient, dialogBackend, compactUrl, layoutBackend, normalization } =
    yield* getGlobalContext();
  const { rsvp: value } = item;

  const config = yield* getItemActionConfig(item, 'joinEvent');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    yield* patchEntity(identity, { rsvp: ON_GOING, pending_invite: null });

    const response = yield apiClient.request({
      method: config.apiMethod || 'put',
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, item)
    });
    const { data } = response.data;

    if (data) {
      const result = normalization.normalize(data);

      yield* fulfillEntity(result.data);
    }

    yield* handleActionFeedback(response);
    keepEntity === false && (yield* deleteEntity(identity));
    yield layoutBackend.reload();
  } catch (error) {
    yield* patchEntity(identity, { rsvp: value });
    yield* handleActionError(error);
  }
}

function* interested(
  action: ItemLocalAction & {
    payload: { keepEntity: boolean };
    meta: { onSuccess: () => void };
  }
) {
  const { identity, keepEntity } = action.payload;
  const { onSuccess } = action?.meta || {};
  const item = yield getItem(identity);

  if (!item) return;

  const {
    apiClient,
    dialogBackend,
    compactUrl,
    layoutBackend,
    normalization,
    getPageParams
  } = yield* getGlobalContext();
  const { user } = yield* getSession();

  if (!user) {
    yield put({
      type: 'user/showDialogLogin',
      meta: { onSuccess }
    });

    return;
  }

  const { rsvp: value } = item;

  const pageParams = getPageParams();

  const config = yield* getItemActionConfig(item, 'interestedEvent');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod || 'put',
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, pageParams)
    });
    const { data } = response.data;

    if (data) {
      const result = normalization.normalize(data);

      yield* fulfillEntity(result.data);
    }

    yield* handleActionFeedback(response);
    keepEntity === false && (yield* deleteEntity(identity));
    yield layoutBackend.reload();
  } catch (error) {
    yield* patchEntity(identity, { rsvp: value });
    yield* handleActionError(error);
  } finally {
    if (onSuccess) onSuccess();
  }
}

function* not_interested(
  action: ItemLocalAction & { payload: { keepEntity: boolean } }
) {
  const { identity, keepEntity } = action.payload;
  const item = yield getItem(identity);

  if (!item) return;

  const { apiClient, dialogBackend, compactUrl, layoutBackend, normalization } =
    yield* getGlobalContext();
  const { rsvp: value } = item;

  const config = yield* getItemActionConfig(item, 'notInterestedEvent');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    yield* patchEntity(identity, {
      rsvp: NOT_INTERESTED,
      pending_invite: null
    });

    const response = yield apiClient.request({
      method: config.apiMethod || 'put',
      url: compactUrl(config.apiUrl, item),
      data: config.apiParams
    });

    const { data } = response.data;

    if (data) {
      const result = normalization.normalize(data);

      yield* fulfillEntity(result.data);
    }

    yield* handleActionFeedback(response);

    keepEntity === false && (yield* deleteEntity(identity));
    yield layoutBackend.reload();
  } catch (error) {
    yield* patchEntity(identity, { rsvp: value });
    yield* handleActionError(error);
  }
}

function* duplicateEvent(
  action: ItemLocalAction & {
    payload: { identity: string };
  }
) {
  const {
    payload: { identity }
  } = action;

  const { dialogBackend, compactUrl } = yield* getGlobalContext();
  const item = yield* getItem(identity);
  const config = yield* getItemActionConfig(item, 'duplicateEvent');

  try {
    yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource: {
          apiUrl: compactUrl(config.apiUrl, item)
        }
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeLatest('joinEvent', going),
  takeLatest('interestedEvent', interested),
  takeLatest('notInterestedEvent', not_interested),
  takeLatest('event/duplicateEvent', duplicateEvent)
];

export default sagas;
