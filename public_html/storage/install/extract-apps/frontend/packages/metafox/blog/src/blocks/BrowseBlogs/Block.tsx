/**
 * @type: block
 * name: blog.block.BrowseBlogs
 * title: Browse Blogs
 * keywords: blog
 * description: Display listing blogs.
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'blog',
    itemProps: { showActionMenu: true }
  },
  defaults: {
    title: 'Blogs',
    itemView: 'blog.itemView.mainCard',
    blockLayout: 'Main Listings',
    gridLayout: 'Blog - Main Card'
  }
});
