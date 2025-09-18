/**
 * @type: block
 * name: group.block.composer
 * title: Composer Group
 * keywords: group
 * description: Display composer group
 */

import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'feed.block.statusComposer',
  name: 'GroupComposer',
  overrides: {
    showWhen: [
      'and',
      ['falsy', 'profile.is_muted'],
      ['falsy', 'profile.is_pending']
    ]
  }
});
