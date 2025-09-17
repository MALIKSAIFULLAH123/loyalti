import { createReducer } from '@reduxjs/toolkit';
import { ADMINCP_SITE_LOADING } from '../constants';

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
    ADMINCP_SITE_LOADING,
    (state: State, { payload }: UpdateAction) => {
      state.loading = payload?.loading;
    }
  );
});
