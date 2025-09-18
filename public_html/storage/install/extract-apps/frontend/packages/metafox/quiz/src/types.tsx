import {
  EmbedItemInFeedItemProps,
  ItemShape,
  ItemViewProps
} from '@metafox/ui';

export interface QuizItemShape extends ItemShape {
  title: string;
  description: string;
  total_play?: number;
}

export type QuizItemActions = {
  approveQuiz: () => void;
  deleteQuiz: () => void;
};

export type QuizItemProps = ItemViewProps<QuizItemShape, QuizItemActions>;

export type QuizItemState = {
  menuOpened?: boolean;
};

export type EmbedQuizInFeedItemProps = EmbedItemInFeedItemProps<QuizItemShape>;

export type AppState = {};
