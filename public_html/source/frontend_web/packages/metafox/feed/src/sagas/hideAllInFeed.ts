/**
 * @type: saga
 * name: feed.hideAllInFeed
 */
import {
  getGlobalContext,
  getItem,
  getResourceConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { FeedItemShape } from '@metafox/feed';
import { getFeedWithUserIdentitySelector } from '@metafox/feed/selectors/feed';
import { takeEvery, select, all, call } from 'redux-saga/effects';
import {
  APP_FEED,
  HIDE_ALL_OWNER,
  HIDE_ALL_SHARED_OWNER,
  HIDE_ALL_SHARED_USER,
  HIDE_ALL_USER,
  RESOURCE_SNOOZE
} from '../constant';
import { compactData } from '@metafox/utils';

export function* getFeedWithUserIdentify(
  user_identity: string,
  feed_identity: string
): Generator<unknown, FeedItemShape[], unknown> {
  return (yield select(
    getFeedWithUserIdentitySelector,
    user_identity,
    feed_identity
  )) as any;
}

export function* setHiddenFeedFromIdentity(item) {
  try {
    yield* patchEntity(item, { invisible: true });
    // eslint-disable-next-line no-empty
  } catch (error) {}
}

export function* undoHiddenFeedFromIdentity(item) {
  try {
    yield* patchEntity(item, { invisible: false });
    // eslint-disable-next-line no-empty
  } catch (error) {}
}

export function* hideAllUser(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const user = yield* getItem(item.user);

  const config = yield* getResourceConfig(
    APP_FEED,
    RESOURCE_SNOOZE,
    'snoozeForever'
  );

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const { is_hidden_type: hideValue, is_just_hide: justHideValue } = item;
  const arrayFeed = yield* getFeedWithUserIdentify(item.user, identity);

  try {
    yield* patchEntity(identity, {
      is_hidden_type: HIDE_ALL_USER,
      is_just_hide: true
    });
    yield all(arrayFeed.map(item => call(setHiddenFeedFromIdentity, item)));

    yield apiClient.request({
      method: config?.apiMethod,
      url: config.apiUrl,
      data: compactData(config?.apiParams, user)
    });
  } catch (error) {
    yield* patchEntity(identity, {
      is_hidden_type: hideValue,
      is_just_hide: justHideValue
    });
    yield* handleActionError(error);
  }
}

export function* undoHideAllUser(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const user = yield* getItem(item.user);

  const config = yield* getResourceConfig(
    APP_FEED,
    RESOURCE_SNOOZE,
    'unSnooze'
  );

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
      url: config.apiUrl,
      data: compactData(config?.apiParams, user)
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

export function* hideAllOwner(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const parent_user = yield* getItem(item.parent_user);

  const config = yield* getResourceConfig(
    APP_FEED,
    RESOURCE_SNOOZE,
    'snoozeForever'
  );

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const { is_hidden_type: hideValue, is_just_hide: justHideValue } = item;
  const arrayFeed = yield* getFeedWithUserIdentify(item.parent_user, identity);

  try {
    yield* patchEntity(identity, {
      is_hidden_type: HIDE_ALL_OWNER,
      is_just_hide: true
    });
    yield all(arrayFeed.map(item => call(setHiddenFeedFromIdentity, item)));

    yield apiClient.request({
      method: config?.apiMethod,
      url: config.apiUrl,
      data: compactData(config?.apiParams, parent_user)
    });
  } catch (error) {
    yield* patchEntity(identity, {
      is_hidden_type: hideValue,
      is_just_hide: justHideValue
    });
    yield* handleActionError(error);
  }
}

export function* undoHideAllOwner(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const parent_user = yield* getItem(item.parent_user);

  const config = yield* getResourceConfig(
    APP_FEED,
    RESOURCE_SNOOZE,
    'unSnooze'
  );

  if (!config?.apiUrl) {
    return yield dialogBackend.comingSoon();
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  const { is_hidden_type: hideValue, is_just_hide: justHideValue } = item;
  const arrayFeed = yield* getFeedWithUserIdentify(item.parent_user, identity);

  try {
    yield* patchEntity(identity, {
      is_hidden_type: null,
      is_just_hide: false
    });
    yield all(arrayFeed.map(item => call(undoHiddenFeedFromIdentity, item)));

    const response = yield apiClient.request({
      method: config?.apiMethod,
      url: config.apiUrl,
      data: compactData(config?.apiParams, parent_user)
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

export function* hideAllSharedUser(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const embed_object = yield* getItem(item.embed_object);

  const shared_user = yield* getItem(embed_object.user);

  const config = yield* getResourceConfig(
    APP_FEED,
    RESOURCE_SNOOZE,
    'snoozeForever'
  );

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
      is_hidden_type: HIDE_ALL_SHARED_USER,
      is_just_hide: true
    });
    yield all(arrayFeed.map(item => call(setHiddenFeedFromIdentity, item)));

    yield apiClient.request({
      method: config?.apiMethod,
      url: config.apiUrl,
      data: compactData(config?.apiParams, shared_user)
    });
  } catch (error) {
    yield* patchEntity(identity, {
      is_hidden_type: hideValue,
      is_just_hide: justHideValue
    });
    yield* handleActionError(error);
  }
}

export function* undoHideAllSharedUser(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const embed_object = yield* getItem(item.embed_object);

  const shared_user = yield* getItem(embed_object.user);

  const config = yield* getResourceConfig(
    APP_FEED,
    RESOURCE_SNOOZE,
    'unSnooze'
  );

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
      url: config.apiUrl,
      data: compactData(config?.apiParams, shared_user)
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

export function* hideAllSharedOwner(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const embed_object = yield* getItem(item.embed_object);

  const shared_owner = yield* getItem(embed_object.parent_user);

  const config = yield* getResourceConfig(
    APP_FEED,
    RESOURCE_SNOOZE,
    'snoozeForever'
  );

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
      is_hidden_type: HIDE_ALL_SHARED_OWNER,
      is_just_hide: true
    });
    yield all(arrayFeed.map(item => call(setHiddenFeedFromIdentity, item)));

    yield apiClient.request({
      method: config?.apiMethod,
      url: config.apiUrl,
      data: compactData(config?.apiParams, shared_owner)
    });
  } catch (error) {
    yield* patchEntity(identity, {
      is_hidden_type: hideValue,
      is_just_hide: justHideValue
    });
    yield* handleActionError(error);
  }
}

export function* undoHideAllSharedOwner(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const embed_object = yield* getItem(item.embed_object);

  const shared_owner = yield* getItem(embed_object.parent_user);

  const config = yield* getResourceConfig(
    APP_FEED,
    RESOURCE_SNOOZE,
    'unSnooze'
  );

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
      url: config.apiUrl,
      data: compactData(config?.apiParams, shared_owner)
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
  takeEvery('unfollowPoster', hideAllUser),
  takeEvery('undoUnfollowPoster', undoHideAllUser),
  takeEvery('unfollowOwner', hideAllOwner),
  takeEvery('undoUnfollowOwner', undoHideAllOwner),
  takeEvery('unfollowSharedPoster', hideAllSharedUser),
  takeEvery('undoUnfollowSharedPoster', undoHideAllSharedUser),
  takeEvery('unfollowSharedOwner', hideAllSharedOwner),
  takeEvery('undoUnfollowSharedOwner', undoHideAllSharedOwner)
];

export default sagas;
