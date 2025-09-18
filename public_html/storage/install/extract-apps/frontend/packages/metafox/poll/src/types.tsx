import { BlockViewProps } from '@metafox/framework';
import {
  EmbedItemInFeedItemProps,
  ItemExtraShape,
  ItemShape,
  ItemViewProps
} from '@metafox/ui';

export interface PollItemShape extends ItemShape {
  question: string;
  module_name: string;
  description: string;
  total_vote: number;
  close_time?: string;
  answers?: string[];
  text?: string;
  extra: ItemExtraShape & {
    can_vote?: boolean;
    can_change_vote?: boolean;
    can_view_result?: boolean;
    can_view_result_after_vote?: boolean;
    can_view_result_before_vote?: boolean;
  };
  attachments?: Record<string, any>[];
  is_user_voted?: boolean;
  is_multiple?: boolean;
  public_vote?: boolean;
  is_closed?: boolean;
}

export interface PollAnswerShape {
  resource_name: string;
  answer: string;
  total_votes: number;
  ordering: number;
  vote_percentage: number;
  id: number;
}

export type PollItemActions = {
  deletePoll: () => void;
};

export type PollItemProps = ItemViewProps<PollItemShape, PollItemActions> & {
  answers: PollAnswerShape[];
  attachments: Record<string, any>;
};

export type PollItemState = {
  menuOpened?: boolean;
};

export type EmbedPollInFeedItemProps = EmbedItemInFeedItemProps<PollItemShape>;

export type AppState = {};

export type PollDetailViewProps = PollItemProps & BlockViewProps;
