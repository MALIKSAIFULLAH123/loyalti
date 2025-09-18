/**
 * @type: saga
 * name: group.saga.announcement
 */
import {
  getGlobalContext,
  getItem,
  handleActionError,
  ItemLocalAction,
  getItemActionConfig,
  fulfillEntity,
  handleActionFeedback,
  pagination,
  makeDirtyPaging,
  patchEntity
} from '@metafox/framework';
import { compactData } from '@metafox/utils';
import { takeEvery, takeLatest } from 'redux-saga/effects';
import { APP_GROUP, GROUP_ANNOUNCEMENT } from '../constant';

export function* removeAnnouncement(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);

  const { apiClient, normalization } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_ANNOUNCEMENT, module_name: APP_GROUP },
    'removeAnnouncement'
  );
  const { apiMethod, apiUrl, apiParams } = config;
  const parent_user = yield* getItem(item?.parent_user);

  if (!item) return;

  try {
    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, { ...item, group_id: parent_user.id })
    });
    const data = response?.data?.data;

    const result = normalization.normalize(data);

    yield* fulfillEntity(result.data);
    yield* makeDirtyPaging('groupAnnouncement', 'feed', false);
    yield* handleActionFeedback(response);
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* markAsAnnouncement(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);

  const { apiClient, normalization } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_ANNOUNCEMENT, module_name: APP_GROUP },
    'markAsAnnouncement'
  );
  const { apiMethod, apiUrl, apiParams } = config;
  const parent_user = yield* getItem(item?.parent_user);

  if (!item) return;

  try {
    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, { ...item, group_id: parent_user?.id })
    });
    const data = response?.data?.data;

    const result = normalization.normalize(data);

    yield* fulfillEntity(result.data);
    yield* makeDirtyPaging('groupAnnouncement', 'feed', false);
    yield* handleActionFeedback(response);
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* getAnnouncementList(action?: {
  type?: string;
  meta?: { onSuccess?: (data?: any) => {} };
  payload?: Record<string, any>;
}) {
  const { getPageParams } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: GROUP_ANNOUNCEMENT, module_name: APP_GROUP },
    'viewAll'
  );
  const { onSuccess } = action.meta || {};

  const pageParams = getPageParams();
  const pagingId =
    action?.payload?.pagingId || `groupAnnouncement/${pageParams.id}`;

  try {
    yield* pagination({
      pagingId,
      apiUrl: config.apiUrl,
      lastIdMode: true,
      apiParams: {
        // page: action.payload,
        group_id: pageParams.id
      }
    });

    if (onSuccess) {
      onSuccess();
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* getAnnouncementPage(action: { type: string; payload }) {
  const { apiClient, normalization } = yield* getGlobalContext();

  const url = action.payload;

  if (!url) return;

  try {
    const response = yield apiClient.request({
      url
    });

    const data = response?.data?.data;
    const result = normalization.normalize(data);

    yield* fulfillEntity(result.data);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* markAsRead(action: {
  type: string;
  payload;
  meta: { onSuccess: () => {} };
}) {
  const identity = action.payload;

  const item = yield* getItem(identity);
  const { id } = item;
  const { onSuccess } = action?.meta || {};
  const { apiClient, getPageParams } = yield* getGlobalContext();

  const pageParams = getPageParams();

  const group_id = parseInt(pageParams.id);

  const config = yield* getItemActionConfig(
    { resource_name: GROUP_ANNOUNCEMENT, module_name: APP_GROUP },
    'markAsRead'
  );
  const { apiUrl, apiMethod, apiParams } = config;

  try {
    yield apiClient.request({
      method: apiMethod || 'POST',
      url: apiUrl,
      data: compactData(apiParams, { id, group_id })
    });
    yield* patchEntity(identity, { is_marked_read: true });

    if (onSuccess) {
      onSuccess();
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

const sagaEffect = [
  takeEvery('announcementItem', markAsAnnouncement),
  takeEvery('removeAnnouncementItem', removeAnnouncement),
  takeLatest('group-announcement/list', getAnnouncementList),
  takeLatest('group-announcement/getPage', getAnnouncementPage),
  takeLatest('group-announcement/markAsRead', markAsRead)
];

export default sagaEffect;
