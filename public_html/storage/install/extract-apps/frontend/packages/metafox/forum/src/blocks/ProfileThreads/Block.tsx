/**
 * @type: block
 * name: forum_thread.block.ProfileThreads
 * title: Profile Threads
 * keywords: profile
 * description: Display profile threads
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'ProfileThreads',
  extendBlock: 'core.block.listview',
  defaults: {
    contentType: 'thread',
    title: 'Threads',
    itemView: 'forum_thread.itemView.profileCard'
  },
  overrides: {
    errorPage: 'default'
  }
});
