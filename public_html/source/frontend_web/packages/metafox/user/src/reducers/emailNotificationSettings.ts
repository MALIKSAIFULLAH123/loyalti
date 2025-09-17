import produce, { Draft } from 'immer';
import { AppState } from '../types';

type State = Partial<AppState['emailNotificationSettings']>;

export default produce(
  (draft: Draft<State>, action) => {
    switch (action.type) {
      case 'setting/emailNotificationSettings/FULFILL':
        draft.data = action.payload;
        draft.loaded = true;
        break;
      case 'setting/emailNotificationSettings/UPDATE': {
        const { var_name, value } = action.payload;
        const index = draft.data.findIndex(item => var_name === item.var_name);

        if (-1 < index) draft.data[index].value = value;
      }
    }
  },
  { loaded: false, data: [], error: undefined }
);
