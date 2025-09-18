/**
 * @type: saga
 * name: story.saga.viewArchive
 */

import {
  fulfillEntity,
  getGlobalContext,
  getPagingSelector,
  getResourceAction,
  getSession,
  LocalAction,
  PagingState
} from '@metafox/framework';
import { compactData } from '@metafox/utils';
import { put, takeLatest, select } from 'redux-saga/effects';
import {
  APP_STORY,
  prefixPagingId_archive,
  RESOURCE_STORY,
  STORY_ARCHIVE_PAGINATION
} from '../constants';
import { converEndDate, converStartDate } from '../utils';
import { isEmpty, isFunction } from 'lodash';
import moment from 'moment';

export function* viewArchives() {
  const { navigate } = yield* getGlobalContext();
  const session = yield* getSession();

  const { user } = session || {};

  if (!user) return;

  navigate(`user/${user.id}/story-archive`);
}

export function* loadStoryArchiveSuccess(
  action: LocalAction<
    {
      identity?: string;
      id?: string;
      direction?: 'next' | 'prev';
    },
    { ids: string[]; pagingId: string; pagesOffset?: any }
  >
) {
  const {
    payload,
    meta: { ids, pagesOffset }
  } = action;
  const { normalization } = yield* getGlobalContext();
  const { id } = payload || {};

  const result = normalization.normalize([
    {
      module_name: 'story',
      resource_name: 'story_archive',
      stories: ids,
      id,
      ...pagesOffset
    }
  ]);

  yield* fulfillEntity(result.data);
}

export function* loadStoryArchive(
  action: LocalAction<
    {
      story_id: string;
      user_id: string;
      date?: string;
      direction?: 'next' | 'prev';
    },
    {
      onSuccess?: (value: any) => void;
      onError?: () => void;
    }
  >
) {
  const { payload, meta } = action;
  const { story_id, user_id, date, direction } = payload || {};
  const { onSuccess, onError } = meta || {};

  const config = yield* getResourceAction(
    APP_STORY,
    RESOURCE_STORY,
    'viewArchives'
  );

  if (!config || !user_id) return;

  const archiveDate = moment(date).format('L');

  const pagingId = `${prefixPagingId_archive}/${archiveDate}`;

  const state: PagingState = yield select(state =>
    getPagingSelector(state, pagingId)
  );

  if (!isEmpty(state) && state.ended) {
    const data = {
      ...state,
      pagingId
    };
    isFunction(onSuccess) && onSuccess(data);

    return;
  }

  yield put({
    type: STORY_ARCHIVE_PAGINATION,
    payload: {
      apiUrl: config.apiUrl,
      apiParams: compactData(config.apiParams, {
        story_id,
        id: user_id,
        from_date: converStartDate(date),
        to_date: converEndDate(date)
      }),
      pagingId,
      direction
    },
    meta: {
      onSuccess,
      onError,
      successAction: {
        type: 'story/story_archive/LOAD_SUCCESS',
        payload: { id: pagingId }
      }
    }
  });
}

const sagas = [
  takeLatest('viewArchives', viewArchives),
  takeLatest('story/story_archive/LOAD', loadStoryArchive),
  takeLatest('story/story_archive/LOAD_SUCCESS', loadStoryArchiveSuccess)
];

export default sagas;
