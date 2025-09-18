/**
 * @type: reducer
 * name: background-status
 */
import produce, { Draft } from 'immer';
import { AppState } from '../types';
import { uniqBy } from 'lodash';
import { combineReducers, createEntityReducer } from '@metafox/framework';
import { APP_BACKGROUND_STATUS } from '../constants';

const collections = produce(
  (draft: Draft<AppState['collections']>, action) => {
    switch (action.type) {
      case 'bgstatus/FULFILL': {
        const { payload } = action;
        draft.collections = payload.collections;
        break;
      }
      case 'bgstatus/loadmore/loading': {
        draft.loading = true;
        break;
      }
      case 'bgstatus/loadmore/end': {
        draft.ended = true;
        break;
      }
      case 'bgstatus/ADD_COLLECTIONS': {
        const { payload } = action;

        if (!payload.collections) return;

        draft.collections = uniqBy(
          [...draft.collections, ...payload.collections],
          'id'
        );
        draft.loading = false;
        draft.page = payload.page;
        break;
      }
    }
  },
  {
    loaded: false,
    collections: [],
    loading: false,
    ended: false
  }
);

export default combineReducers({
  entities: createEntityReducer(APP_BACKGROUND_STATUS),
  collections
});
