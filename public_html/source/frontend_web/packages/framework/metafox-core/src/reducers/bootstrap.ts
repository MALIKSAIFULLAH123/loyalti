/**
 * @type: reducer
 * name: bootstrap
 */
import { createReducer } from '@reduxjs/toolkit';
import { APP_BOOTSTRAP_DONE } from '../constants';

type State = {
  loaded: boolean;
  error?: string;
};

type UpdateAction = {
  type: typeof APP_BOOTSTRAP_DONE;
  payload: {
    loaded: boolean;
    error?: string;
  };
};

export default createReducer<State>(
  {
    loaded: false
  },
  builder => {
    builder.addCase(
      APP_BOOTSTRAP_DONE,
      (state: State, { payload }: UpdateAction) => {
        state.loaded = payload.loaded;
        state.error = payload.error;
      }
    );
  }
);
