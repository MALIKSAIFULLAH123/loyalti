/**
 * @type: block
 * name: group.block.relatedGroup
 * title: Profile Group
 * keywords: group
 * description: related groups
 * thumbnail:
 */
 import { createBlock, ListViewBlockProps } from '@metafox/framework';

 export default createBlock<ListViewBlockProps>({
   name: 'blockRelatedGroups',
   extendBlock: 'core.block.listview',
   defaults: {
     itemView: 'group.itemView.mainCard'
   }
 });