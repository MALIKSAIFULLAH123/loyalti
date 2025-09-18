import { BlockViewProps } from '@metafox/framework';
import {
    ItemShape,
    ItemViewProps
  } from '@metafox/ui';

export interface AppState {
    entities: {
      sevent: Record<string, SeventItemShape>;
      isPlaying: string;
    };
  }
  export interface SeventItemShape extends Omit<ItemShape, 'user'> {
    title: string;
    time_to_read: string;
    audio_link: string;
    description: string;
    user: string;
    is_favourite: string;
    text: string;
    is_draft?: boolean;
    categories?: string[];
    attachments: string[];
    tags?: string[];
  }
  export type SeventItemProps = ItemViewProps<
    SeventItemShape
    > & {
    isModalView?: boolean;
};
export type SeventDetailViewProps = SeventItemProps & BlockViewProps;