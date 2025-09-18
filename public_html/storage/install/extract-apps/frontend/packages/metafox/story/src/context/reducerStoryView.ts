import { PauseStatus, StoryUserProps } from '../types';
import produce, { Draft } from 'immer';
import { StoryContextProps } from './StoryViewContext';

type Action =
  | {
      type: 'setListUserStories';
      payload: StoryUserProps[];
    }
  | {
      type: 'setReactions';
      payload: any[];
    }
  | {
      type: 'setIdentityStoryActive';
      payload: string;
    }
  | {
      type: 'setIdentityUserS_Active';
      payload: string;
    }
  | {
      type: 'setForcePause';
      payload: PauseStatus;
    }
  | {
      type: 'setOpenStoryDetail';
      payload: boolean;
    }
  | {
      type: 'setOpenViewComment';
      payload: boolean;
    }
  | {
      type: 'setOpenActionItem';
      payload: boolean;
    }
  | {
      type: 'setReadyStateFile';
      payload: boolean;
    }
  | {
      type: 'setProgressVideoPlay';
      payload: number;
    }
  | {
      type: 'setIndexStoryActive';
      payload: number;
    }
  | {
      type: 'setLastStory';
      payload: boolean;
    }
  | {
      type: 'setReady';
      payload: boolean;
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
    };

export const reducerStoryView = produce(
  (draft: Draft<StoryContextProps>, action: Action) => {
    switch (action.type) {
      case 'setListUserStories':
        draft.listUserStories = action.payload;
        break;
      case 'setReactions':
        draft.reactions = action.payload;
        break;
      case 'setIdentityStoryActive':
        draft.identityStoryActive = action.payload;
        break;
      case 'setIdentityUserS_Active':
        draft.identityUserStoryActive = action.payload;
        break;
      case 'setForcePause':
        draft.pauseStatus = action.payload;
        break;
      case 'setOpenStoryDetail':
        draft.openStoryDetail = action.payload;
        break;
      case 'setOpenViewComment':
        draft.openViewComment = action.payload;
        break;
      case 'setOpenActionItem':
        draft.openActionItem = action.payload;
        break;
      case 'setReadyStateFile':
        draft.readyStateFile = action.payload;
        break;
      case 'setProgressVideoPlay':
        draft.progressVideoPlay = action.payload;
        break;
      case 'setIndexStoryActive':
        draft.indexStoryActive = action.payload;
        break;
      case 'setLastStory':
        draft.isLastStory = action.payload;
        break;
      case 'setReady':
        draft.isReady = action.payload;
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
    }
  }
);
