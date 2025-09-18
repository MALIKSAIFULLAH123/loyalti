import React from 'react';
import { PauseStatus, StoryUserProps } from '../types';

export interface StoryContextProps {
  identityStoryActive: string;
  identityUserStoryActive: string;
  listUserStories: StoryUserProps[];
  pauseStatus: PauseStatus;
  reactions: any[];
  indexStoryActive: any;
  openStoryDetail: boolean;
  openViewComment: boolean;
  openActionItem: boolean;
  readyStateFile: boolean;
  progressVideoPlay: number;
  isLastStory: boolean;
  isReady: boolean;
  fire?: any;
  mutedStatus?: boolean;
  durationVideo?: number;
  buffer?: boolean;
  // archive
  total?: any;
  prevDate?: string;
  nextDate?: string;
  stories?: string[];
  loading?: boolean;
  positionStory?: string;
  pagingId?: string;
  date?: string;
}

const StoryViewContext = React.createContext<StoryContextProps>({});

export default StoryViewContext;
