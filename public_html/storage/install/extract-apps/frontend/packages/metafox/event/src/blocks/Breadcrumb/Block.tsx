/**
 * @type: block
 * name: event.block.breadcrumb
 * title: Breadcrumb Event
 * keywords: event
 * description:
 * experiment: true
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
