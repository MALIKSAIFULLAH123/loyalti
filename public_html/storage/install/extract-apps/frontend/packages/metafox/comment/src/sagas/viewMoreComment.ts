/**
 * @type: saga
 * name: comment.viewMoreComments
 */

import {
  getItem,
  patchEntity,
  getResourceAction,
  handleActionError,
  getGlobalContext,
  fulfillEntity,
  getPagingSelector,
  PagingPayload,
  PagingMeta
} from '@metafox/framework';
import { takeEvery, select, put } from 'redux-saga/effects';
import {
  SortTypeValue,
  SortTypeModeValue,
  SORT_RELEVANT,
  getValueSortTypeMode,
  getKeyDataBySortType,
  getListingDataBySortType,
  getInitDataBySortType,
  getUpdateReplyContain,
  COMMENT_PAGINATION,
  COMMENT_ACTION_INIT,
  COMMENT_ACTION_CHANGE_SORT
} from '@metafox/comment';
import { isEmpty } from 'lodash';

function paginationAction(payload: PagingPayload, meta?: PagingMeta) {
  return {
    type: COMMENT_PAGINATION,
    payload,
    meta
  };
}

function* pagination(payload: PagingPayload, meta?: PagingMeta) {
  yield put(paginationAction(payload, meta));
}

type ViewMoreCommentAction = {
  type: string;
  payload: {
    identity: string;
    sortType: SortTypeValue;
    sortSettingDefault: SortTypeValue;
    pagingId?: string;
    lastId?: string;
    viewmoreSortTypeMode?: SortTypeModeValue;
  };
};

type ViewMoreCommentSuccessAction = {
  type: string;
  payload: {
    identity: string;
    sortType: SortTypeValue;
    viewmoreSortTypeMode: SortTypeModeValue;
  };
  meta: { ids: string[]; onSuccess?: () => void };
};

type InitCommentAction = {
  type: string;
  payload: {
    identity: string;
    sortType?: SortTypeValue;
    sortTypeInit: SortTypeValue;
    pagingId?: string;
  };
  meta: Record<string, any>;
};

export function* initComment(action: InitCommentAction) {
  const { payload, meta } = action;
  const { identity, sortTypeInit } = payload;
  const keyData = getKeyDataBySortType(sortTypeInit);

  if (!identity) return;

  const item = yield* getItem(identity);

  if (!item) return null;

  const { related_comments, children } = item;

  yield* patchEntity(identity, {
    [keyData]: related_comments || children || []
  });

  if (meta?.onSuccess) {
    meta.onSuccess();
  }
}

export function* viewMoreComments(action: ViewMoreCommentAction) {
  const {
    identity,
    sortType,
    sortSettingDefault,
    pagingId: pagingIdProp
  } = action.payload;
  const { onSuccess } = action?.meta || {};
  const viewmoreSortTypeMode = getValueSortTypeMode(sortType);
  const pagingId =
    pagingIdProp ||
    `comment/${viewmoreSortTypeMode}/${identity.replace(/\./g, '_')}`;

  if (!identity) return;

  const item = yield* getItem(identity);

  if (!item) return null;

  const pagingData = yield select(state => getPagingSelector(state, pagingId));

  const {
    comment_type_id,
    item_type,
    resource_name,
    comment_item_id,
    item_id,
    id
  } = item;

  let excludesComment = item.excludesComment ?? [];
  const relevant_comments = item.relevant_comments ?? [];
  let dataCurrent = getListingDataBySortType(pagingData, item, sortType);

  if (SORT_RELEVANT === sortType) {
    dataCurrent = dataCurrent.slice(1);
    const first_id =
      relevant_comments[0] && typeof relevant_comments[0] === 'string'
        ? relevant_comments[0].split('.')[3]
        : undefined;
    excludesComment = [...excludesComment, first_id];
  }

  const last_id =
    dataCurrent[dataCurrent.length - 1] &&
    typeof dataCurrent[dataCurrent.length - 1] === 'string'
      ? dataCurrent[dataCurrent.length - 1].split('.')[3]
      : undefined;

  yield* pagination(
    {
      pagingId,
      apiUrl: '/comment',
      apiParams: {
        item_type: comment_type_id || item_type || resource_name,
        item_id: comment_item_id || item_id || id,
        limit: 10,
        excludes: excludesComment,
        last_id,
        sort_type: viewmoreSortTypeMode
      }
    },
    {
      successAction: {
        type: 'comment/viewMoreComments/SUCCESS',
        payload: { identity, sortType, sortSettingDefault },
        meta: { onSuccess }
      }
    }
  );
}

export function* viewMoreReplies(action: ViewMoreCommentAction) {
  const { payload, meta = {} } = action;
  const {
    identity,
    pagingId: pagingIdProp,
    lastId,
    viewmoreSortTypeMode
  } = payload;
  const { onSuccess } = meta;
  const pagingId = pagingIdProp || `comment/${identity.replace(/\./g, '_')}`;

  if (!identity) return;

  const item = yield* getItem(identity);

  if (!item) return;

  const item_type =
    item.comment_item_type || item.item_type || item.resource_name;
  const item_id = item.comment_item_id || item.item_id || item.id;

  const excludesComment = item.excludesComment ?? [];

  yield* pagination(
    {
      pagingId,
      mode: 'two_direction_load',
      apiUrl: '/comment',
      apiParams: {
        item_type,
        item_id,
        parent_id: item.id,
        limit: 10,
        excludes: excludesComment,
        sort_type: viewmoreSortTypeMode,
        last_id: lastId
      }
    },
    {
      successAction: {
        type: 'comment/viewMoreReplyComments/SUCCESS',
        payload: { identity, viewmoreSortTypeMode },
        meta: { onSuccess }
      }
    }
  );
}

function* viewMoreCommentsSuccess(action: ViewMoreCommentSuccessAction) {
  const { identity } = action.payload;
  const { ids, onSuccess } = action.meta;
  const item = yield* getItem(identity);

  if (!item) return null;

  if (!ids) return null;

  if (onSuccess) {
    onSuccess();
  }
}

function* viewMoreReplyCommentsSuccess(action: ViewMoreCommentSuccessAction) {
  const { identity, viewmoreSortTypeMode } = action.payload;
  const { ids, onSuccess } = action.meta;
  const item = yield* getItem(identity);

  if (!item) return null;

  if (!ids) return null;

  if (item.reply_detail_statistics) {
    const reply_detail_statistics = getUpdateReplyContain(
      item.reply_detail_statistics,
      viewmoreSortTypeMode,
      ids.length
    );

    yield* patchEntity(identity, {
      reply_detail_statistics
    });
  }

  if (onSuccess) {
    onSuccess();
  }
}

function* changeSort({ payload, meta }: ItemLocalAction) {
  const { identity, sortType, excludes } = payload;

  if (sortType === SORT_RELEVANT) return;

  const { onSuccess, onStart } = meta || {};
  const item = yield* getItem(identity);
  const data = getInitDataBySortType(item, sortType);
  const keyData = getKeyDataBySortType(sortType);
  const { apiClient, compactUrl, compactData, normalization, getSetting } =
    yield* getGlobalContext();
  const totalPrefetchCommentsDefault: number = getSetting(
    'comment.prefetch_comments_on_feed'
  );
  const { statistic } = item || {};

  if (
    !item ||
    !isEmpty(data) ||
    totalPrefetchCommentsDefault === 0 ||
    statistic?.total_comment === 0
  ) {
    return;
  }

  if (onStart) {
    onStart();
  }

  const config = yield* getResourceAction(
    'comment',
    'comment',
    'getRelatedComments'
  );

  const {
    comment_type_id,
    item_type,
    resource_name,
    comment_item_id,
    item_id,
    id
  } = item;

  if (!config?.apiUrl) return;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod || 'get',
      url: compactUrl(config.apiUrl, item),
      params: compactData(config.apiParams, {
        ...item,
        item_type: comment_type_id || item_type || resource_name,
        item_id: comment_item_id || item_id || id,
        sort_type: getValueSortTypeMode(sortType),
        excludes
      })
    });

    if (response?.data) {
      const data = response.data?.data;
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
      yield* patchEntity(identity, {
        [keyData]: result.ids ?? []
      });

      if (onSuccess) {
        onSuccess();
      }
    }

    return true;
  } catch (error) {
    yield* handleActionError(error);
  }

  return false;
}

const sagas = [
  takeEvery('comment/viewMoreComments', viewMoreComments),
  takeEvery('comment/viewMoreReplies', viewMoreReplies),
  takeEvery('comment/viewMoreComments/SUCCESS', viewMoreCommentsSuccess),
  takeEvery(
    'comment/viewMoreReplyComments/SUCCESS',
    viewMoreReplyCommentsSuccess
  ),
  takeEvery(COMMENT_ACTION_CHANGE_SORT, changeSort),
  takeEvery(COMMENT_ACTION_INIT, initComment)
];

export default sagas;
