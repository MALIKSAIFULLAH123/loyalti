/**
 * @type: saga
 * name: announcement.getAnnouncementList
 */

import {
  deleteEntity,
  getGlobalContext,
  getItem,
  getResourceAction,
  handleActionError,
  patchEntity,
  ItemLocalAction,
  getItemActionConfig,
  PAGINATION_INIT
} from '@metafox/framework';
import { takeLatest, put } from 'redux-saga/effects';
import {
  decreaseTotalAnnouncement,
  updateTotalAnnouncement
} from '../reducers/statistic';

function* updateStatistic(action: {
  type: string;
  payload: { type?: string };
  meta: any;
}) {
  const { type } = action.payload || {};
  const meta = action.meta || {};

  try {
    if (type === PAGINATION_INIT) {
      yield put({
        type: 'announcement/loaded'
      });
    }

    if (meta?.pagesOffset?.total) {
      yield put(updateTotalAnnouncement(meta?.pagesOffset?.total));
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* markAsRead(action: { type: string; payload }) {
  const { id, isDetail } = action.payload;

  const identity = `announcement.entities.announcement.${id}`;

  try {
    const item = yield* getItem(identity);

    if (!item) return null;

    const { module_name, resource_name, statistic } = item;
    const { apiUrl, apiMethod } = yield* getResourceAction(
      module_name,
      resource_name,
      'markAsRead'
    );

    const { apiClient } = yield* getGlobalContext();

    yield apiClient.request({
      method: apiMethod || 'POST',
      url: apiUrl,
      data: { announcement_id: item.id }
    });

    if (item.can_be_closed && !isDetail) {
      yield* deleteEntity(identity);
    } else {
      yield* patchEntity(identity, {
        is_read: true,
        statistic: {
          ...statistic,
          total_view: statistic?.total_view + 1
        }
      });
    }

    if (item.can_be_closed) {
      yield put(decreaseTotalAnnouncement());
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* openListViewer({ payload }: ItemLocalAction) {
  const { identity } = payload;

  const item = yield* getItem(identity);

  if (!item) return;

  const { dialogBackend, compactData } = yield* getGlobalContext();

  const dataSource = yield* getItemActionConfig(item, 'viewAnalytic');

  try {
    yield dialogBackend.present({
      component: 'announcement.dialog.listViewer',
      props: {
        dialogTitle: 'read_by',
        apiUrl: dataSource.apiUrl,
        apiParams: compactData(dataSource.apiParams, item),
        pagingId: `announcement/openListViewer${item.id}`
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* closeAnnouncement(action: {
  type: string;
  payload: { identity: string; onSuccess: any };
}) {
  const { identity, onSuccess } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, compactUrl } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(item, 'closeAnnouncement');

  if (!item) return;

  try {
    yield apiClient.request({
      method: config.apiMethod || 'PUT',
      url: compactUrl(config.apiUrl, item),
      data: { announcement_id: item.id }
    });

    if (onSuccess) onSuccess();

    yield* deleteEntity(identity);
    yield put(decreaseTotalAnnouncement());
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeLatest('announcement/updateStatistic', updateStatistic),
  takeLatest('announcement/markAsRead', markAsRead),
  takeLatest('announcement/openListViewer', openListViewer),
  takeLatest('announcement/close', closeAnnouncement)
];

export default sagas;
