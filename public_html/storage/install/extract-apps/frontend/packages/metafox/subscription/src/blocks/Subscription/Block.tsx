/**
 * @type: block
 * name: subscription.block.profileSubscription
 * title: Subscription Block
 * keywords: subscription
 * description: Subscription profile
 */

import { createBlock, ListViewBlockProps } from '@metafox/framework';
import {
  APP_SUBSCRIPTION,
  RESOURCE_SUBSCRIPTION_INVOICE
} from '@metafox/subscription';
import Base from './Base';

export default createBlock<ListViewBlockProps>({
  name: 'PagesListingBlock',
  extendBlock: Base,
  defaults: {
    title: 'my_subscription',
    itemView: 'subscription_invoice.itemView.activeCard',
    blockLayout: 'Profile - Side Contained (no header divider)',
    gridLayout: 'Subscription - List',
    itemLayout: 'Subscription - List - Profile',
    emptyPage: 'hide',
    moduleName: APP_SUBSCRIPTION,
    resourceName: RESOURCE_SUBSCRIPTION_INVOICE,
    actionName: 'viewMyActiveSubscription',
    displayLimit: 1
  }
});