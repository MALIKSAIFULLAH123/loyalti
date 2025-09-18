/**
 * @type: block
 * name: event.block.overviewOwner
 * title: Group Overview In Event
 * keywords: event
 * description: Display group overview in event.
 */

import { connectSubject, createBlock } from '@metafox/framework';
import {
  actionCreators,
  connectItemView
} from '@metafox/event/hocs/connectInviteItem';
import Base from './Base';

const Enhance = connectSubject(connectItemView(Base, actionCreators));

export default createBlock<any>({
  extendBlock: Enhance
});
