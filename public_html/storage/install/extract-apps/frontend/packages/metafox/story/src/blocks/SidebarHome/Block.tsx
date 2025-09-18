/**
 * @type: block
 * name: story.block.sidebarHomeStory
 * title: Home Sidebar
 * keywords: sidebar
 * description:
 * thumbnail:
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';
import { PAGINATION_STORY_LIST } from '@metafox/story/constants';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    blockLayout: 'sidebar home story',
    itemView: 'story.itemView.storyAvatar',
    itemLayout: 'Story - Cards',
    gridLayout: 'Story - Cards',
    moduleName: 'story',
    resourceName: 'story',
    actionName: 'viewAll',
    canLoadMore: true,
    canLoadSmooth: true,
    clearDataOnUnMount: true,
    pagingId: PAGINATION_STORY_LIST,
    emptyPageProps: {
      title: 'no_stories_found',
      contentStyle: {
        sx: {
          paddingTop: '8px',
          paddingBottom: '8px'
        }
      }
    }
  }
});
