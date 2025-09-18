export type BgStatusItemShape = {
  id: number;
  view_id: number;
  ordering: number;
  is_deleted: boolean;
  image: Record<'48' | '300' | '1024', string>;
  text_color?: string;
};

export interface BgStatusCollectionShape {
  id: number;
  name: string;
  is_active: boolean;
  is_deleted: boolean;
  view_id: number;
  image: Record<'48' | '300' | '1024', string>;
  backgrounds: BgStatusItemShape[];
}

export type BgStatusItemProps = {};

export type BgStatusListBaseProps = {
  displayLimit?: number;
  selectedId?: number;
  onSelectItem?: (item: unknown) => void;
  onClear?: () => void;
};

export type AppState = {
  entities: {
    status_background: Record<string, BgStatusItemShape>;
  };
  collections: {
    loading: boolean;
    page: number;
    ended: boolean;
    collections: BgStatusCollectionShape[];
  };
};
