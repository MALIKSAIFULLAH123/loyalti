/**
 * @type: reducer
 * name: pageMeta
 */
import { PageMetaShape } from '@metafox/layout';
import { createReducer } from '@reduxjs/toolkit';
import { PageMetaDataState } from '../types';
import { PAGEMETA_REFRESH } from '@metafox/framework';

type State = PageMetaDataState;

type UpdateAction = {
  type: 'pageMeta/put';
  payload: {
    id: string;
    data: PageMetaShape;
  };
};

type RefreshAction = {
  type: typeof PAGEMETA_REFRESH;
  payload: {
    id: string;
    prefix?: string;
  };
};

export default createReducer<PageMetaDataState>({}, builder => {
  builder.addCase('pageMeta/put', (state: State, { payload }: UpdateAction) => {
    state[payload.id] = payload.data;
  });
  builder.addCase(
    PAGEMETA_REFRESH,
    (state: State, { payload }: RefreshAction) => {
      const { prefix, id } = payload || {};

      if (prefix) {
        Object.keys(state).forEach(pageId => {
          if (pageId.startsWith(prefix)) {
            state[pageId] = undefined;
          }
        });
      } else if (id) {
        state[id] = undefined;
      }
    }
  );
});
