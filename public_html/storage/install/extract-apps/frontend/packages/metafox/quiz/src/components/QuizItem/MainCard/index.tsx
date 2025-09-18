/**
 * @type: itemView
 * name: quiz.itemView.mainCard
 * chunkName: quiz
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/quiz/hocs/connectQuizItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
