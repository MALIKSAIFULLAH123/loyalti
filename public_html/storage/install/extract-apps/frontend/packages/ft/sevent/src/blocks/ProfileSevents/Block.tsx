/**
 * @type: block
 * name: sevent.block.ProfileSevents
 * title: Profile Sevents
 * keywords: sevent, profile
 * description: display Profile Sevents
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'ProfileSevents',
  extendBlock: 'core.block.listview',
  overrides: {
    errorPage: 'default'
  },
  defaults: {
    contentType: 'sevent',
    dataSource: {
      apiUrl: '/sevent'
    },
    title: 'Sevents',
    itemView: 'sevent.itemView.mainCard'
  }
});
