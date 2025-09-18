/**
 * @type: block
 * name: video.block.userVideoListingBlock
 * title: Videos User
 * keywords: video
 * description: Display video items in profile user.
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const UserVideoListingBlock = createBlock<ListViewBlockProps>({
  name: 'userVideoListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'video',
    dataSource: { apiUrl: '/video' },
    errorPage: 'default'
  },
  defaults: {
    title: 'Videos',
    itemView: 'video.itemView.profileCard',
    gridContainerProps: { spacing: 2 },
    gridItemProps: { xs: 12, sm: 12, md: 6, lg: 6, xl: 6 },
    emptyPage: 'core.block.no_content'
  }
});

export default UserVideoListingBlock;
