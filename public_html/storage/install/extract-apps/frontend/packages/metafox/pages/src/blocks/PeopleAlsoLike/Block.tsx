/**
 * @type: block
 * name: pages.block.peopleAlsoLike
 * title: Pages
 * keywords: page
 * description: People Also Like
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'PagesListingBlock',
  extendBlock: 'core.block.listview',
  defaults: {
    itemView: 'page.itemView.mainCard'
  }
});
