/**
 * @type: saga
 * name: comment.saga.deleteComment
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
  patchEntity
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* deleteComment(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const canDelete = item.extra?.can_delete;

  if (!canDelete) return;

  const config = yield* getItemActionConfig(item, 'deleteItem');

  if (!config.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });

    yield* handleActionFeedback(response);
    yield* deleteEntity(identity);

    const data = response.data.data;
    const extra = {
      parentIdentity: item?.parent_id
        ? `comment.entities.comment.${item.parent_id}`
        : undefined,
      statistic: item.statistic
    };

    // update resource feed
    if (data?.feed_id) {
      yield* updateData(`feed.entities.feed.${data.feed_id}`, extra);
    }

    // update resource origin
    const module = data.alternative_item_module_id || data.item_module_id;
    const resource = data.alternative_item_type || data.item_type;
    const id = data.alternative_item_id || data.item_id;

    if (resource && id) {
      // eslint-disable-next-line max-len
      const resourceIdentity = `${module}.entities.${resource}.${id}`;
      yield* updateData(resourceIdentity, extra);
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* updateData(identity: string, extra?: Record<string, any>) {
  if (!identity) return;

  try {
    const { parentIdentity, statistic: delItemStatistic } = extra || {};
    const item = yield* getItem(identity);
    const parentComment = yield* getItem(parentIdentity);

    if (!item) return;

    const { statistic } = item || {};

    if (parentIdentity) {
      const { statistic } = parentComment || {};

      yield* patchEntity(parentIdentity, {
        statistic: {
          ...statistic,
          total_comment: statistic.total_comment - 1
        }
      });
    }

    const newData = parentIdentity
      ? {
          ...statistic,
          total_comment: statistic.total_comment - 1,
          total_reply: statistic.total_reply - 1
        }
      : {
          ...statistic,
          total_comment:
            statistic.total_comment - 1 - delItemStatistic?.total_comment,
          total_reply: statistic.total_reply - delItemStatistic?.total_comment
        };

    yield* patchEntity(identity, {
      statistic: newData
    });
  } catch (err) {}
}

const sagas = [takeEvery('deleteComment', deleteComment)];

export default sagas;
