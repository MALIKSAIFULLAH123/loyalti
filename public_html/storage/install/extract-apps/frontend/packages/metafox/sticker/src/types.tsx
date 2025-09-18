export type OnStickerClick = (value: number) => void;

export interface StickerSetShape {
  id: number;
  resource_name: string;
  module_name: string;
  title: string;
  is_active: boolean;
  is_default: boolean;
  is_added: boolean;
  thumbnail_id: number;
  ordering: number;
  image: {
    image_url: string;
  };
  is_recent_set: boolean;
  statistic: {
    total_sticker: number;
    used: number;
  };
  stickers: string[];
  _identity: string;
}

export interface StickerItemShape {
  resource_name: string;
  module_name: string;
  ordering: number;
  view_only: boolean;
  is_deleted: boolean;
  image: string;
  thumbs?: {
    origin: string;
  }
  id: number;
  _identity: string;
}

export interface AttachStickerButtonProps {
  title?: string;
  icon?: string;
  onStickerClick?: OnStickerClick;
  multiple?: boolean;
}

export interface AppState {
  entities: {
    sticker_set: Record<string, StickerSetShape>;
    sticker: Record<string, StickerSetShape>;
  };
  myStickerSet: {
    recent: string[];
    data: string[];
    loaded: boolean;
    loading: boolean;
  };
  stickerSet: {
    data: StickerSetShape[];
    loaded: boolean;
  };
  myStickerRecent: {
    data: string[];
    loaded: boolean;
    loading: boolean;
  }
}
