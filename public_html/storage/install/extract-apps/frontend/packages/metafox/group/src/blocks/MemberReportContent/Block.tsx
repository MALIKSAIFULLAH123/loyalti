/**
 * @type: block
 * name: group.manager.reportContent
 * title: Group - Manage - Member-Reported Content
 * keywords: group
 * description: Group Member-Reported Content
 * thumbnail:
 * experiment: true
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Base';

export default createBlock<ListViewBlockProps>({
  name: 'GroupReportContent',
  extendBlock: Base,
  defaults: {
    title: 'member_reported_content',
    itemView: 'group.itemView.reportedPost',
    blockLayout: 'App List Pending Posts',
    gridLayout: 'Group - ReportedPost - Main Card',
    itemLayout: 'Group - ReportedPost - Main Card'
  }
});
