import { BlockViewProps } from '@metafox/framework';
import {
  EmbedItemInFeedProps,
  ImageMatureState,
  ItemShape,
  ItemViewProps
} from '@metafox/ui';

export interface VideoItemShape extends ItemShape {
  title: string;
  destination: string;
  video_url: string;
  is_processing?: boolean;
  is_failed?: boolean;
  embed_code?: string;
  mature?: ImageMatureState;
}

export interface VideoItemShapeDialog extends ItemShape {
  embed_code: string;
  destination: string;
  video_url: string;
  album?: any;
  categories?: any;
}

export interface VideoItemShapeDialogProps extends BlockViewProps {
  item: VideoItemShapeDialog;
  identity: string;
  searchParams?: Record<string, any>;
  error?: any;
}

export type VideoItemProps = ItemViewProps<VideoItemShape, VideoItemActions>;

export type VideoItemState = {
  menuOpened?: boolean;
};

export type EmbedVideoInFeedItemProps = EmbedItemInFeedProps<VideoItemShape>;

export type AppState = {};
export type VideoItemActions = {};
