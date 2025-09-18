import { AppState } from '@metafox/chatplus/types';
import produce, { Draft } from 'immer';
import { conversionStatusNum2Str } from '../utils';

export default produce((draft: Draft<AppState['session']>, action) => {
  switch (action.type) {
    case 'chatplus/init': {
      draft.user = action.payload.login;
      break;
    }
    // change status
    case 'chatplus/users/updateStatus': {
      const { _id, status, invisible, lastStatusUpdated } = action.payload;

      if (draft?.user?._id !== _id) return;

      draft.user.status = conversionStatusNum2Str(status, invisible);
      draft.user.invisible = invisible;
      draft.user.lastStatusUpdated = lastStatusUpdated;

      break;
    }
  }
}, {});
