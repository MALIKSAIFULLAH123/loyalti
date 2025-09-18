/**
 * @type: block
 * name: event.block.eventHost
 * title: Event Host Detail
 * keywords: event
 * description: Display event hotst detail.
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
  name: 'EventHost',
  defaults: {
    displayLimit: 3,
    headerActions: [
      {
        as: 'event.viewListHostButton',
        showWhen: ['truthy', 'item.extra.can_view_hosts']
      }
    ]
  },
  overrides: {
    showWhen: ['truthy', 'profile.extra.can_view_hosts']
  }
});
