import { ItemShape } from '@metafox/ui';

export interface AppState {
  stepListing: {
    data: string[];
    loaded: boolean;
    total: number;
    page?: number;
  };
}

export type IntroGettingStarted = {
  todo_list_items: TodoItem[],
  welcome_message: string;
}

export type TodoItem = ItemShape & {
  is_done: boolean;
  title: string;
  ordering: number;
  attach_images?: string[];
}