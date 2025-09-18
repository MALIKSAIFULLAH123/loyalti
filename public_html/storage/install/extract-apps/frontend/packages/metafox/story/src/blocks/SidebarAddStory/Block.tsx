/**
 * @type: block
 * name: story.block.sidebarAddStory
 * title: Sidebar Add Story
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
    itemLayout: 'Story Card - Form',
    gridLayout: 'Story - Cards',
    moduleName: 'story',
    displayLimit: 1,
    resourceName: 'story',
    actionName: 'viewAll',
    pagingId: PAGINATION_STORY_LIST
  }
});
