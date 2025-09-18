import { ItemShape, ItemViewProps } from '@metafox/ui';

export interface SavedItemShape extends Omit<ItemShape, 'user'> {
  title: string;
  item_type: string;
  item_type_name?: string;
  belong_to_collection: boolean;
  default_collection_id: string;
  default_collection_name: string;
  user: string;
  collection_ids: Array<number>;
  is_opened?: boolean;
}

export type SavedItemActions = {};

export type SavedItemState = {
  menuOpened: boolean;
};

export type SavedItemProps = ItemViewProps<
  SavedItemShape,
  SavedItemActions,
  SavedItemState
>;

export interface AppState {}
