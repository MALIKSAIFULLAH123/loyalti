/**
 * @type: saga
 * name: feed.snoozeInFeed
 */
import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { takeEvery, all, call } from 'redux-saga/effects';
import {
  SNOOZE_OWNER,
  SNOOZE_SHARED_OWNER,
  SNOOZE_SHARED_USER,
  SNOOZE_USER
} from '../constant';
import {
  getFeedWithUserIdentify,
  setHiddenFeedFromIdentity,
  undoHiddenFeedFromIdentity
} from './hideAllInFeed';

export function* snoozePoster(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const user = yield* getItem(item.user);

  const config = yield* getItemActionConfig(item, 'snooze');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const { is_hidden_type: hideValue, is_just_hide: justHideValue } = item;
  const arrayFeed = yield* getFeedWithUserIdentify(item.user, identity);

  try {
    yield* patchEntity(identity, {
      is_hidden_type: SNOOZE_USER,
      is_just_hide: true
    });
    yield all(arrayFeed.map(item => call(setHiddenFeedFromIdentity, item)));

    yield apiClient.request({
      method: config?.apiMethod,
      url: compactUrl(config?.apiUrl, user)
    });
  } catch (error) {
    yield* patchEntity(identity, {
      is_hidden_type: hideValue,
      is_just_hide: justHideValue
    });
    yield* handleActionError(error);
  }

  // TODO implements call featured api
}

export function* undoSnoozePoster(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const user = yield* getItem(item.user);

  const config = yield* getItemActionConfig(item, 'undoSnooze');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const { is_hidden_type: hideValue, is_just_hide: justHideValue } = item;
  const arrayFeed = yield* getFeedWithUserIdentify(item.user, identity);

  try {
    yield* patchEntity(identity, {
      is_hidden_type: null,
      is_just_hide: false
    });
    yield all(arrayFeed.map(item => call(undoHiddenFeedFromIdentity, item)));

    const response = yield apiClient.request({
      method: config?.apiMethod,
      url: compactUrl(config?.apiUrl, user)
    });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, {
      is_hidden_type: hideValue,
      is_just_hide: justHideValue
    });
    yield* handleActionError(error);
  }
}

export function* snoozeOwner(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const parent_user = yield* getItem(item.parent_user);

  const config = yield* getItemActionConfig(item, 'snooze');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const { is_hidden_type: hideValue, is_just_hide: justHideValue } = item;
  const arrayFeed = yield* getFeedWithUserIdentify(item.parent_user, identity);

  try {
    yield* patchEntity(identity, {
      is_hidden_type: SNOOZE_OWNER,
      is_just_hide: true
    });
    yield all(arrayFeed.map(item => call(setHiddenFeedFromIdentity, item)));

    yield apiClient.request({
      method: config?.apiMethod,
      url: compactUrl(config?.apiUrl, parent_user)
    });
  } catch (error) {
    yield* patchEntity(identity, {
      is_hidden_type: hideValue,
      is_just_hide: justHideValue
    });
    yield* handleActionError(error);
  }

  // TODO implements call featured api
}

export function* undoSnoozeOwner(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const parent_user = yield* getItem(item.parent_user);

  const config = yield* getItemActionConfig(item, 'undoSnooze');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const { is_hidden_type: hideValue, is_just_hide: justHideValue } = item;
  const arrayFeed = yield* getFeedWithUserIdentify(item.parent_user, identity);

  try {
    yield* patchEntity(identity, {
      is_hidden_type: false,
      is_just_hide: false
    });
    yield all(arrayFeed.map(item => call(undoHiddenFeedFromIdentity, item)));

    const response = yield apiClient.request({
      method: config?.apiMethod,
      url: compactUrl(config?.apiUrl, parent_user)
    });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, {
      is_hidden_type: hideValue,
      is_just_hide: justHideValue
    });
    yield* handleActionError(error);
  }
}

export function* snoozeSharedPoster(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const embed_object = yield* getItem(item.embed_object);

  const shared_user = yield* getItem(embed_object.user);

  const config = yield* getItemActionConfig(item, 'snooze');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const { is_hidden_type: hideValue, is_just_hide: justHideValue } = item;
  const arrayFeed = yield* getFeedWithUserIdentify(
    shared_user._identity,
    identity
  );

  try {
    yield* patchEntity(identity, {
      is_hidden_type: SNOOZE_SHARED_USER,
      is_just_hide: true
    });
    yield all(arrayFeed.map(item => call(setHiddenFeedFromIdentity, item)));

    yield apiClient.request({
      method: config?.apiMethod,
      url: compactUrl(config?.apiUrl, shared_user)
    });
  } catch (error) {
    yield* patchEntity(identity, {
      is_hidden_type: hideValue,
      is_just_hide: justHideValue
    });
    yield* handleActionError(error);
  }
}

export function* undoSnoozeSharedPoster(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const embed_object = yield* getItem(item.embed_object);

  const shared_user = yield* getItem(embed_object.user);

  const config = yield* getItemActionConfig(item, 'undoSnooze');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const { is_hidden_type: hideValue, is_just_hide: justHideValue } = item;
  const arrayFeed = yield* getFeedWithUserIdentify(shared_user, identity);

  try {
    yield* patchEntity(identity, {
      is_hidden_type: null,
      is_just_hide: false
    });
    yield all(arrayFeed.map(item => call(undoHiddenFeedFromIdentity, item)));

    const response = yield apiClient.request({
      method: config?.apiMethod,
      url: compactUrl(config?.apiUrl, shared_user)
    });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, {
      is_hidden_type: hideValue,
      is_just_hide: justHideValue
    });
    yield* handleActionError(error);
  }
}

export function* snoozeSharedOwner(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const embed_object = yield* getItem(item.embed_object);

  const shared_owner = yield* getItem(embed_object.parent_user);

  const config = yield* getItemActionConfig(item, 'snooze');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const { is_hidden_type: hideValue, is_just_hide: justHideValue } = item;
  const arrayFeed = yield* getFeedWithUserIdentify(
    shared_owner._identity,
    identity
  );

  try {
    yield* patchEntity(identity, {
      is_hidden_type: SNOOZE_SHARED_OWNER,
      is_just_hide: true
    });
    yield all(arrayFeed.map(item => call(setHiddenFeedFromIdentity, item)));

    yield apiClient.request({
      method: config?.apiMethod,
      url: compactUrl(config?.apiUrl, shared_owner)
    });
  } catch (error) {
    yield* patchEntity(identity, {
      is_hidden_type: hideValue,
      is_just_hide: justHideValue
    });
    yield* handleActionError(error);
  }
}

export function* undoSnoozeSharedOwner(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const embed_object = yield* getItem(item.embed_object);

  const shared_owner = yield* getItem(embed_object.parent_user);

  const config = yield* getItemActionConfig(item, 'undoSnooze');

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const { is_hidden_type: hideValue, is_just_hide: justHideValue } = item;
  const arrayFeed = yield* getFeedWithUserIdentify(shared_owner, identity);

  try {
    yield* patchEntity(identity, {
      is_hidden_type: null,
      is_just_hide: false
    });
    yield all(arrayFeed.map(item => call(undoHiddenFeedFromIdentity, item)));

    const response = yield apiClient.request({
      method: config?.apiMethod,
      url: compactUrl(config?.apiUrl, shared_owner)
    });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, {
      is_hidden_type: hideValue,
      is_just_hide: justHideValue
    });
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('snoozePoster', snoozePoster),
  takeEvery('undoSnoozePoster', undoSnoozePoster),
  takeEvery('snoozeOwner', snoozeOwner),
  takeEvery('undoSnoozeOwner', undoSnoozeOwner),
  takeEvery('snoozeSharedPoster', snoozeSharedPoster),
  takeEvery('undoSnoozeSharedPoster', undoSnoozeSharedPoster),
  takeEvery('snoozeSharedOwner', snoozeSharedOwner),
  takeEvery('undoSnoozeSharedOwner', undoSnoozeSharedOwner)
];

export default sagas;
