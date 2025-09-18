/**
 * @type: block
 * name: activity-point.block.package
 * keyword: activity point
 * title: Activity Points
 */

import {
  APP_ACTIVITY,
  Resource_Activitypoint_Package
} from '@metafox/activity-point/constants';
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'activity-point'
  },
  defaults: {
    itemLayout: 'ActivityPoint - Packages',
    gridLayout: 'ActivityPoint - Packages',
    itemView: 'activitypoint.itemView.packageItem',
    emptyPage: 'core.block.no_content_with_icon',
    emptyPageProps: {
      title: 'no_package_found',
      description: 'there_are_no_ready_package',
      image: 'ico-box'
    },
    moduleName: APP_ACTIVITY,
    resourceName: Resource_Activitypoint_Package,
    actionName: 'viewAll',
    canLoadMore: true,
    canLoadSmooth: true
  }
});
