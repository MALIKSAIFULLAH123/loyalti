import { createReducer, Draft } from '@reduxjs/toolkit';
import { AppState } from '../types';

type State = AppState['roomFiles'];

function createRoomFiles(rid: string) {
  return {
    rid,
    media: {
      files: [],
      count: 0,
      total: 0
    },

    other: {
      files: [],
      count: 0,
      total: 0
    }
  };
}

export default createReducer<State>({}, builder => {
  builder.addCase(
    'chatplus/getRoomFiles',
    (state: Draft<State>, action: any) => {
      const {
        rid,
        files,
        count,
        offset,
        total,
        type: typeFile
      } = action.payload;

      state[rid] = state[rid] || createRoomFiles(rid);

      if (offset === state[rid][typeFile].count) {
        state[rid][typeFile].files = [...state[rid][typeFile].files, ...files];
        state[rid][typeFile].count = state[rid][typeFile].count + count;
        state[rid][typeFile].total = total;
        state[rid][typeFile].init = true;
      }
    }
  );
});
