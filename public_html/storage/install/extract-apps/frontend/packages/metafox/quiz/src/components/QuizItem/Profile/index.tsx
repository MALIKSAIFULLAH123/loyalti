/**
 * @type: itemView
 * name: quiz.itemView.profileCard
 * chunkName: quiz
 */
import {
  actionCreators,
  connectItemView
} from '@metafox/quiz/hocs/connectQuizItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, actionCreators);
