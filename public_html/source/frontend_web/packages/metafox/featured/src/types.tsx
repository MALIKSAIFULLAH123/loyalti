import {
  EmbedItemInFeedItemProps,
  ItemShape,
  ItemViewProps
} from '@metafox/ui';

export interface AppState {
  entities: {
    featured: Record<string, FeaturedItemShape>;
  };
}

export type AdItemProps = ItemViewProps<FeaturedItemShape>;

export type EmbedAdItemInFeedItemProps =
  EmbedItemInFeedItemProps<FeaturedItemShape>;

export interface FeaturedItemShape extends ItemShape {
  [key: string]: any;
}
