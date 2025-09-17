import { createReducer } from '@reduxjs/toolkit';
import {
  ADMINCP_SITE_STATUS_PUT,
  ADMINCP_SITE_STATUS_START
} from '../constants';

type State = Record<string, any>;

type UpdateAction = {
  type: any;
  payload: Record<string, any>;
};

const initialState = {
  loading: false
};

export default createReducer<State>(initialState, builder => {
  builder.addCase(
    ADMINCP_SITE_STATUS_PUT,
    (state: State, { payload }: UpdateAction) => {
      state.data = payload?.data;
      state.loading = false;
    }
  );
  builder.addCase(ADMINCP_SITE_STATUS_START, (state: State) => {
    state.loading = true;
  });
});
