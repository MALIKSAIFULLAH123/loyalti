import { createReducer, Draft } from '@reduxjs/toolkit';
import { isEmpty } from 'lodash';
import { AppState } from '../types';

type State = AppState['calls'];

export default createReducer<State>({}, builder => {
  builder.addCase(
    'chatplus/call/report',
    (state: Draft<State>, { type, payload }: any) => {
      const { callId, type: typeReason } = payload;

      state[callId].reason = typeReason;
    }
  );
  builder.addCase(
    'chatplus/callInfo',
    (state: Draft<State>, { type, payload }: any) => {
      state[payload.callId] = payload;
    }
  );
  builder.addCase(
    'chatplus/onCallChanged',
    (state: Draft<State>, { type, payload }: any) => {
      const [, args] = payload;

      if (isEmpty(args)) return;

      state[args.callId] = { ...state[args.callId], ...args };

      const callStorage = localStorage.getItem('chatplus/callId');

      if (callStorage) {
        const [callId] = callStorage?.split('/');

        if (callId && args.callId === callId) {
          localStorage.setItem(
            'chatplus/callId',
            `${callId}/${args.callStatus}`
          );
        }
      }
    }
  );
});
