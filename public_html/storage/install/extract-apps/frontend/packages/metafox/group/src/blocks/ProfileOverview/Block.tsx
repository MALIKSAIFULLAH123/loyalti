/**
 * @type: block
 * name: group.block.profilePagesListingOverview
 * title: Profile Group
 * keywords: group, profile
 * description:
 * thumbnail:
 * experiment: true
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import { APP_GROUP } from '@metafox/group/constant';

const GroupListingBlock = createBlock<ListViewBlockProps>({
  name: 'GroupListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'group',
    dataSource: {
      apiUrl: `/${APP_GROUP}`,
      apiParams: 'user_id=:id&limit=2'
    }
  },
  defaults: {
    title: 'Groups',
    blockProps: { variant: 'contained' },
    itemView: 'group.itemView.smallCard',
    gridContainerProps: { spacing: 0 },
    gridItemProps: { xs: 12, sm: 12, md: 12 }
  }
});

export default GroupListingBlock;
