/**
 * @type: block
 * name: event.block.eventView
 * title: Event Detail
 * keywords: event
 * description: Display event detail.
 */

import { connectSubject, createBlock } from '@metafox/framework';
import {
  actionCreators,
  connectItemView
} from '@metafox/event/hocs/connectEventDetail';
import Base from './Base';

const Enhance = connectSubject(connectItemView(Base, actionCreators));

export default createBlock<any>({
  extendBlock: Enhance,
  defaults: {
    placeholder: 'Search'
  }
});
