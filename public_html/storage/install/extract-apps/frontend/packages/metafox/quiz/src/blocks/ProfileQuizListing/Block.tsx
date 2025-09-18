/**
 * @type: block
 * name: quiz.block.ProfileQuizListing
 * title: Profile's Quiz Items
 * keywords: quiz, profile
 * description: Display profile's quiz items.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const QuizListingBlock = createBlock<ListViewBlockProps>({
  name: 'QuizListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'quiz',
    errorPage: 'default'
  },
  defaults: {
    title: 'Quizzes',
    itemView: 'quiz.itemView.profileCard',
    dataSource: {
      apiUrl: '/quiz',
      apiParams: 'user_id=:id&limit=6'
    },
    emptyPage: 'core.block.no_content'
  }
});

export default QuizListingBlock;
