/**
 * @type: block
 * name: story.block.storyArchiveListingBlock
 * title: Story Archive
 * keywords:
 * description:
 * thumbnail:
 * experiment: true
 */

import { createBlock, ListViewBlockProps } from '@metafox/framework';
import { APP_STORY, RESOURCE_STORY } from '@metafox/story/constants';

const StoryArchiveListingBlock = createBlock<ListViewBlockProps>({
  name: 'StoryArchiveListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'story',
    errorPage: 'default'
  },
  defaults: {
    title: 'story_archive',
    blockLayout: 'Profile - Contained',
    gridLayout: 'User Story - Archive Card',
    itemLayout: 'User Story - Archive Card',
    itemView: 'story.itemView.storyArchiveCard',
    canLoadSmooth: true,
    moduleName: APP_STORY,
    resourceName: RESOURCE_STORY,
    actionName: 'viewArchives'
  }
});

export default StoryArchiveListingBlock;
