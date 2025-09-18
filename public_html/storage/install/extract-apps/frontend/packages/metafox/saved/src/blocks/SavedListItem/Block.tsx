/**
 * @type: block
 * name: saved.block.SavedListItem
 * title: Saved List Items
 * keywords: saved
 * description: Display saved list items.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Base';

export default createBlock<ListViewBlockProps>({
  name: 'SavedListItem',
  extendBlock: Base,
  overrides: {
    contentType: 'saved'
  },
  defaults: {
    canLoadSmooth: true,
    title: 'saved_items',
    emptyPage: 'core.block.no_content_with_icon',
    emptyPageProps: {
      title: 'no_saved_items_found',
      description: 'no_saved_items_found_description',
      image: 'ico-bookmark-o',
      contentStyle: {
        sx: {
          background: 'transparent'
        }
      }
    },
    itemLayout: 'Saved Lists Collections'
  }
});
