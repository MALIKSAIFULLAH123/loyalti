/**
 * @type: block
 * name: event.block.manageEvent
 * title: Manage Events
 * keywords: event
 * description: Display manage events.
 */

import { connectSubject, createBlock } from '@metafox/framework';
import {
  actionCreators,
  connectItemView
} from '@metafox/event/hocs/connectEventDetail';
import Base from './Base';

const Enhance = connectSubject(connectItemView(Base, actionCreators));

export default createBlock<any>({
  extendBlock: Enhance
});
