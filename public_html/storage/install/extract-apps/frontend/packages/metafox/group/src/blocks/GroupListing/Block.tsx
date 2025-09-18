/**
 * @type: block
 * name: groups.block.groupsListingBlock
 * title: Groups
 * keywords: group
 * description: Display groups
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'GroupsListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {},
  defaults: {
    title: 'Groups',
    emptyPage: 'hide',
    itemView: 'group.itemView.mainCard'
  }
});
