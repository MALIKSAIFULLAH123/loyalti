import {
  EmbedItemInFeedItemProps,
  ItemShape,
  ItemViewProps
} from '@metafox/ui';

export interface AdItemShape extends ItemShape {
  title: string;
  description: string;
}

export type AdItemProps = ItemViewProps<AdItemShape>;

export type EmbedAdItemInFeedItemProps = EmbedItemInFeedItemProps<AdItemShape>;

export interface AppState {
  entities: {
    advertise: Record<string, AdItemShape>;
  };
}

export enum CreationTypeAdvertise {
  IMAGE = 'image',
  HTML = 'html'
}

export enum TypePlacement {
  PayPerClick = 'ppc',
  CostPerMille = 'cpm'
}
