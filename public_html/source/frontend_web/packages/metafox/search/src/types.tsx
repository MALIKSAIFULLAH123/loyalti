import { ItemShape, ItemViewProps } from '@metafox/ui';

export interface SearchItemShape extends ItemShape {
  title: string;
  description: string;
  item_id: number;
  item_type: string;
  resource_link: string;
}

export type SearchItemProps = ItemViewProps<SearchItemShape>;

export interface SuggestionItem {
  note: string;
  to: string;
  title: string;
  image: string;
  resource_name: string;
}

export interface SuggestionShape {
  data: SuggestionItem[];
  loaded?: boolean;
}

export interface AppState {
  suggestions?: Record<string, SuggestionShape>;
  recentSearch: {
    data: string[];
    loaded: boolean;
  };
}
