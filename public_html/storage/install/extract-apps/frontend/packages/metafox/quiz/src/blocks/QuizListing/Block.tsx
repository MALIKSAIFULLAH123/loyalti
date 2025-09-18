/**
 * @type: block
 * name: quiz.block.quizListingBlock
 * title: Quiz Items
 * keywords: quiz
 * description: Display quiz items.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'QuizListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'quiz',
    dataSource: { apiUrl: '/quiz' }
  },
  defaults: {
    title: 'Quizzes',
    itemView: 'quiz.itemView.mainCard'
  }
});
