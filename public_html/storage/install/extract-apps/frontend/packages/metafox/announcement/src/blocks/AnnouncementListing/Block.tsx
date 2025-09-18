/**
 * @type: block
 * name: announcement.block.announcementListing
 * title: Announcement Listing
 * keywords: announcement
 * description: Display announcement listing.
 * chunkName: announcement
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  name: 'AnnouncementListing',
  defaults: {
    gridLayout: 'Announcement - Slider',
    itemLayout: 'Announcement - Slider'
  },
  overrides: { showWhen: ['truthy', 'acl.announcement.announcement.view'] }
});
