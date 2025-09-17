import produce, { Draft } from 'immer';
import { AppState } from '../types';

type State = Partial<AppState['invisibleSettings']>;

export default produce(
  (draft: Draft<State>, action) => {
    switch (action.type) {
      case 'setting/invisibleSettings/FULFILL':
        draft.data = action.payload;
        draft.loaded = true;
        break;
      case 'setting/invisibleSettings/update':
        draft.data.value = action.payload;
        break;
    }
  },
  {
    data: {},
    loaded: false,
    error: undefined
  }
);
