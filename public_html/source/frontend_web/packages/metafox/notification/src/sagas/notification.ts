/**
 * @type: saga
 * name: notification
 */
import {
  deleteEntity,
  fulfillEntity,
  getGlobalContext,
  getItem,
  getResourceAction,
  ItemLocalAction,
  patchEntity,
  makeDirtyPaging,
  handleActionFeedback,
  handleActionError,
  IS_ADMINCP,
  MFOX_ADMINCP_URL,
  handleActionConfirm
} from '@metafox/framework';
import { select, takeEvery, all, call, put } from 'redux-saga/effects';
import { cloneDeep, isEmpty } from 'lodash';
import {
  APP_NOTIFICATION,
  RESOURCE_NOTIFICATION,
  NOTIFICATION_POPPER_PAGING_IDS
} from '@metafox/notification';
import { compactUrl } from '@metafox/utils';

const IsUrlReg = /^(http|https)?:?\/\//s;

export function* markAsRead(action: ItemLocalAction) {
  const { identity } = action.payload;
  const { compactUrl } = yield* getGlobalContext();

  try {
    const { apiUrl, apiMethod } = yield* getResourceAction(
      APP_NOTIFICATION,
      RESOURCE_NOTIFICATION,
      'markAsRead'
    );
    const item = yield* getItem(identity);

    if (!item || !apiUrl) return null;

    yield* patchEntity(identity, { is_read: true });

    const { apiClient } = yield* getGlobalContext();

    if (item?.module_item) {
      yield* makeDirtyPaging(item.module_item);
    }

    yield apiClient.request({
      method: apiMethod || 'PUT',
      url: compactUrl(apiUrl, item)
    });
  } catch (err) {
    // handle error
  }
}
export function* markAsUnread(action: ItemLocalAction) {
  const { identity } = action.payload;
  const { compactUrl } = yield* getGlobalContext();

  try {
    const { apiUrl, apiMethod } = yield* getResourceAction(
      APP_NOTIFICATION,
      RESOURCE_NOTIFICATION,
      'markAsUnread'
    );
    const item = yield* getItem(identity);

    if (!item || !apiUrl) return null;

    yield* patchEntity(identity, { is_read: false });

    const { apiClient } = yield* getGlobalContext();

    yield apiClient.request({
      method: apiMethod || 'PUT',
      url: compactUrl(apiUrl, item)
    });
  } catch (err) {
    // handle error
  }
}

export function* markAllAsRead(action: ItemLocalAction) {
  const { apiClient } = yield* getGlobalContext();

  try {
    const { apiUrl, apiMethod } = yield* getResourceAction(
      APP_NOTIFICATION,
      RESOURCE_NOTIFICATION,
      'markAllAsRead'
    );

    const data = yield select(
      state => state.notification.entities.notification
    );

    const cloneData = cloneDeep(data);

    Object.keys(cloneData).forEach(id => (cloneData[id].is_read = true));

    yield* fulfillEntity({
      notification: {
        entities: { notification: cloneData }
      }
    });

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl
    });
    yield* handleActionFeedback(response);
  } catch (err) {
    console.log(err);
    // handle error
  }
}

export function* editNotificationSetting({ meta }: ItemLocalAction) {
  try {
    if (meta?.setLocalState) {
      meta.setLocalState(prev => ({ ...prev, menuOpened: false }));
    }

    const { navigate } = yield* getGlobalContext();

    if (navigate) {
      navigate('/settings/notifications');
    }
  } catch (err) {
    // handle error
  }
}

export function* browseNotifications() {
  const { navigate } = yield* getGlobalContext();

  try {
    if (navigate) {
      navigate('/notification');
    }
  } catch (err) {
    // handle error
  }
}

export function* getUnread() {
  const { apiClient } = yield* getGlobalContext();

  try {
    yield apiClient.get('/notification');
  } catch (err) {
    // handle error
  }
}

export function* deleteItem(action: ItemLocalAction) {
  try {
    const { apiClient } = yield* getGlobalContext();
    const { apiUrl, apiMethod } = yield* getResourceAction(
      APP_NOTIFICATION,
      RESOURCE_NOTIFICATION,
      'deleteItem'
    );
    const { identity } = action.payload;
    const item = yield* getItem(identity);

    if (!item || !apiUrl) return null;

    yield* deleteEntity(identity);

    const response = yield apiClient.request({
      method: apiMethod || 'DELETE',
      url: compactUrl(apiUrl, item)
    });
    yield* handleActionFeedback(response);
  } catch (err) {
    // handle error
  }
}

export function* viewNotification(action: ItemLocalAction) {
  const { navigate } = yield* getGlobalContext();
  const { identity } = action.payload;

  try {
    const item = yield* getItem(identity);

    if (!item?.link) return null;

    if (item?.link?.includes('feed')) {
      yield* makeDirtyPaging('feed');
    }

    if (item?.module_item) {
      yield* makeDirtyPaging(item.module_item);
    }

    if (
      IsUrlReg.test(item?.link) ||
      IS_ADMINCP ||
      (!IS_ADMINCP && item?.link && item?.link?.startsWith(MFOX_ADMINCP_URL))
    ) {
      window.open(item?.link);

      return;
    }

    navigate(item.link, { state: { asModal: item?.asModal } });
  } catch (err) {
    // handle error
    console.log(err);
  }
}

export function* deleteAll(action: ItemLocalAction<{ pagingId?: string }>) {
  const { apiClient } = yield* getGlobalContext();
  const config = yield* getResourceAction(
    APP_NOTIFICATION,
    RESOURCE_NOTIFICATION,
    'deleteAll'
  );

  const { pagingId } = action.payload || {};

  const pagingItem = yield* getItem(
    `pagination.${pagingId || NOTIFICATION_POPPER_PAGING_IDS}`
  );

  if (isEmpty(pagingItem?.ids) || !config.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    yield all(pagingItem?.ids.map(id => call(deleteEntity, id)));

    const response = yield apiClient.request({
      method: config.apiMethod || 'delete',
      url: config.apiUrl
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* clickItem() {
  try {
    yield put({
      type: 'core/status/clearNotification'
    });
    // eslint-disable-next-line no-empty
  } catch (error) {}
}

const sagas = [
  takeEvery('notification/deleteItem', deleteItem),
  takeEvery('notification/viewItem', viewNotification),
  takeEvery('notification/markAsRead', markAsRead),
  takeEvery('notification/markAsUnread', markAsUnread),
  takeEvery('notification/editNotificationSetting', editNotificationSetting),
  takeEvery('notification/browseNotifications', browseNotifications),
  takeEvery('notification/deleteAll', deleteAll),
  takeEvery('notification/markAllAsRead', markAllAsRead),
  takeEvery('notification/menu/clickItem', clickItem)
];

export default sagas;
