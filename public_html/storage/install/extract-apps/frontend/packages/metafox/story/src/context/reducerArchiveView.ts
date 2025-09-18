import { PauseStatus, StoryArchiveProps } from '../types';
import produce, { Draft } from 'immer';
import { StoryContextProps } from './StoryViewContext';

type Action =
  | {
      type: 'setInit';
      payload: StoryArchiveProps;
    }
  | {
      type: 'setReady';
      payload: boolean;
    }
  | {
      type: 'setStories';
      payload: any[];
    }
  | {
      type: 'setStoryActive';
      payload: any;
    }
  | {
      type: 'setReadyStateFile';
      payload: boolean;
    }
  | {
      type: 'setForcePause';
      payload: PauseStatus;
    }
  | {
      type: 'setOpenActionItem';
      payload: boolean;
    }
  | {
      type: 'setProgressVideoPlay';
      payload: number;
    }
  | {
      type: 'setMuted';
      payload: boolean;
    }
  | {
      type: 'setDuration';
      payload: number;
    }
  | {
      type: 'setBuffer';
      payload: boolean;
    }
  | {
      type: 'setReactions';
      payload: any[];
    }
  | {
      type: 'setLoading';
      payload: boolean;
    }
  | {
      type: 'setOpenStoryDetail';
      payload: boolean;
    };

export const reducerArchiveView = produce(
  (draft: Draft<StoryContextProps>, action: Action) => {
    switch (action.type) {
      case 'setInit':
        draft = Object.assign(draft, action.payload);
        break;
      case 'setStoryActive':
        draft.identityStoryActive = action.payload?.identity;
        draft.indexStoryActive = action.payload?.index;
        draft.positionStory = action.payload?.positionStory;
        draft.loading = false;

        break;
      case 'setReady':
        draft.isReady = action.payload;
        break;
      case 'setStories':
        draft.stories = action.payload;
        break;
      case 'setReadyStateFile':
        draft.readyStateFile = action.payload;
        break;
      case 'setForcePause':
        draft.pauseStatus = action.payload;
        break;
      case 'setReactions':
        draft.reactions = action.payload;
        break;
      case 'setOpenActionItem':
        draft.openActionItem = action.payload;
        break;
      case 'setProgressVideoPlay':
        draft.progressVideoPlay = action.payload;
        break;
      case 'setMuted':
        draft.mutedStatus = action.payload;
        break;
      case 'setDuration':
        draft.durationVideo = action.payload;
        break;
      case 'setBuffer':
        draft.buffer = action.payload;
        break;
      case 'setLoading':
        draft.loading = action.payload;
        break;
      case 'setOpenStoryDetail':
        draft.openStoryDetail = action.payload;
        break;
    }
  }
);
