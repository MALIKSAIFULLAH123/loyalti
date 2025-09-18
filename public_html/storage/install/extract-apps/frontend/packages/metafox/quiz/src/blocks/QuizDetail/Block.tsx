/**
 * @type: block
 * name: quiz.block.quizView
 * title: Quiz Detail
 * keywords: quiz
 * description: Display quiz detail
 */

import { connectSubject, createBlock } from '@metafox/framework';
import { actionCreators, connectItemView } from '../../hocs/connectQuizItem';
import Base from './Base';

const Enhance = connectSubject(
  connectItemView(Base, actionCreators, {
    quiz_question: true,
    attachments: true
  })
);

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    placeholder: 'Search',
    blockLayout: 'Detail - Paper - Radius Bottom'
  }
});
