import { createReducer, Draft } from '@reduxjs/toolkit';
import { AppState } from '../types';

type State = AppState['entities']['room'];

export default createReducer<State>({}, builder => {
  builder.addCase(
    'chatplus/room/UPDATE',
    (state: Draft<State>, { type, payload }: any) => {
      state[payload._id] = payload;
    }
  );
});
