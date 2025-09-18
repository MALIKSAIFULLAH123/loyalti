/**
 * @type: block
 * name: forum_post.block.detailListingBlock
 * title: Posts
 * keywords: forum
 * description: Display detail listing posts
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  defaults: {
    itemView: 'forum_post.itemView.detailCard',
    pageName: 'forum_thread.view',
    contentType: 'forum_post',
    canLoadMore: true,
    loadMoreType: 'pagination',
    messagePagination: 'showing_from_to_of_total_replies',
    lastReadMode: true,
    numberOfItemsPerPage: 10,
    blockLayout: 'Detail Post Listing',
    canLoadSmooth: true,
    gridLayout: 'Forum_thread - Main Card',
    emptyPage: 'hide',
    noHeader: true,
    itemLayout: 'Forum_Post - Detail Card',
    dataSource: {
      apiParams: 'thread_id=:id&post_id=:post_id',
      apiUrl: '/forum-post'
    }
  }
});
