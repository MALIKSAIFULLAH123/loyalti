/**
 * @type: block
 * name: poll.block.pollView
 * title: Poll Detail
 * keywords: poll
 * description: Display poll detail
 */

import { connectSubject, createBlock } from '@metafox/framework';
import { actionCreators, connectItemView } from '../../hocs/connectPollItem';
import Base from './Base';

const Enhance = connectSubject(
  connectItemView(Base, actionCreators, {
    poll_answer: true,
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
