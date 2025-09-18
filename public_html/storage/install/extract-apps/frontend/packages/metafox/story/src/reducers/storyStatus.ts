import { AppState } from '@metafox/story/types';
import produce, { Draft } from 'immer';

export default produce((draft: Draft<AppState['storyStatus']>, action) => {
  switch (action.type) {
    case 'story/updateMutedStatus': {
      draft.muted = action.payload;
      draft.isEditMuted = true;
      break;
    }
  }
}, {});
