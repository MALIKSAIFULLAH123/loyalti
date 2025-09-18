/**
 * @type: embedView
 * name: quiz.embedItem.insideFeedItem
 * chunkName: feed_embed
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/quiz/hocs/connectQuizItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators, {
  quiz_question: true
});
