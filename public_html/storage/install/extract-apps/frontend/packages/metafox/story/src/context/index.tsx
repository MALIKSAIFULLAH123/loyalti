import { PauseStatus } from '../types';
import { StoryContextProps } from './StoryViewContext';

export { default as AddFormContext } from './AddFormContext';
export { default as StoryViewContext } from './StoryViewContext';

export const initStateStory: StoryContextProps = {
  identityStoryActive: undefined,
  identityUserStoryActive: undefined,
  listUserStories: [],
  pauseStatus: PauseStatus.No,
  reactions: [],
  indexStoryActive: undefined,
  openStoryDetail: false,
  openViewComment: false,
  openActionItem: false,
  readyStateFile: false,
  progressVideoPlay: 0,
  isLastStory: false,
  isReady: false,
  mutedStatus: true,
  buffer: false
};
