import produce, { Draft } from 'immer';
import { AppState } from '../types';

export default produce((draft: Draft<AppState['settings']>, action) => {
  switch (action.type) {
    case 'chatplus/init':
      Object.assign(draft, action.payload?.publicSettings);
      break;
    default:
      return draft;
  }
}, {});
