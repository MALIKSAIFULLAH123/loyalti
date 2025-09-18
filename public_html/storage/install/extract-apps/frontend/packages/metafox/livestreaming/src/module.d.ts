import '@metafox/framework/Manager';
import { AppState } from './types';
import LivestreamingSocket from '@metafox/livestreaming/services/LiveStreamingSocket';

declare module '@metafox/framework/Manager' {
  interface Manager {
    // add more services
    CommentItemViewLiveStreaming?: React.FC<{
      identity: string;
      itemLive?: Record<string, any>;
      setParentReply?: () => void;
    }>;
    livestreamingSocket?: LivestreamingSocket;
  }
  interface GlobalState {
    livestreaming?: AppState;
  }
}
