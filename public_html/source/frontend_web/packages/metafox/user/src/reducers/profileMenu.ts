import produce, { Draft } from 'immer';
import { AppState } from '../types';

type State = Partial<AppState['profileMenu']>;

export default produce(
  (draft: Draft<State>, action) => {
    switch (action.type) {
      case 'setting/profileMenu/FULFILL':
        draft.data = action.payload;
        draft.loaded = true;
        break;
      case 'setting/profileMenu/UPDATE': {
        const { var_name, value } = action.payload;

        if (draft.data[var_name]) draft.data[var_name].default = value;
      }
    }
  },
  { data: [], loaded: false, error: undefined }
);
