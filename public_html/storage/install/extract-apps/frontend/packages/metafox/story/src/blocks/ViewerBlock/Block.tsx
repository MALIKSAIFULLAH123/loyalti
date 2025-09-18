/**
 * @type: block
 * name: story.block.viewListBlock
 * title: Story Viewer Listing Block
 * keywords:
 * description:
 * thumbnail:
 * experiment: true
 */
import { ListViewBlockProps, createBlock } from '@metafox/framework';
import { APP_STORY, RESOURCE_STORY_VIEW } from '@metafox/story/constants';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  overrides: {
    canLoadMore: true
  },
  defaults: {
    itemView: 'story.itemView.storyViewer',
    moduleName: APP_STORY,
    resourceName: RESOURCE_STORY_VIEW,
    actionName: 'viewAll',
    emptyPage: 'hide',
    errorPage: 'hide',
    clearDataOnUnMount: true,
    canLoadMore: true,
    canLoadSmooth: true,
    blockLayout: 'App List',
    itemLayout: 'Story - Viewer',
    gridLayout: 'Story - Viewer'
  }
});
