import { PopperProps } from '@mui/material';

export type ImageShape = {
  width?: number;
  height?: number;
  url?: string;
};
// https://developers.giphy.com/docs/api/schema#image-object
export type ImageType = {
  original?: ImageShape;
  fixed_width?: ImageShape;
  fixed_width_small?: ImageShape;
};

export interface GiphyItemShape {
  giphy_gif_id: string;
  embed_url?: string;
  url?: string;
  title: string;
  type?: string;
  images?: ImageType;
}
export interface AppState {
  entities: {
    giphy: Record<string, GiphyItemShape>;
  };
}

export type OnGifClick = (value: string) => void;

export interface AttachGifButtonProps {
  title?: string;
  icon?: string;
  placement?: PopperProps['placement'];
  onGifClick?: OnGifClick;
}
