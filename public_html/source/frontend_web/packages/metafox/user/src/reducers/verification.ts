import { LocalAction } from '@metafox/framework';
import { createReducer } from '@reduxjs/toolkit';
import { AppState } from '../types';

type State = AppState['verify'];

export default createReducer<State>({ loading: true }, builder => {
  builder.addCase(
    'user/verification/update',
    (state, action: LocalAction<State>) => {
      state.loading = action.payload.loading;
      state.error = action.payload.error;
      state.success = action.payload.success;
    }
  );
});
