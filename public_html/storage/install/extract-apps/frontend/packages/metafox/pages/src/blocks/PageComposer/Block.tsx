/**
 * @type: block
 * name: page.block.composer
 * title: Composer Page
 * keywords: page
 * description: Display composer page
 */

import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'feed.block.statusComposer',
  name: 'PageComposer',
  overrides: {
    showWhen: ['falsy', 'profile.is_pending']
  }
});
