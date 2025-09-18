/**
 * @type: route
 * name: groups.add
 * path: /group/add
 * chunkName: pages.group
 * bundle: web
 */
import { createEditingPage } from '@metafox/framework';
import { groupPreviewChanged } from '@metafox/group/constant';

export default createEditingPage({
  appName: 'group',
  resourceName: 'group',
  pageName: 'groups.add',
  changeEventName: groupPreviewChanged,
  disableFormOnSuccess: true,
  breadcrumbs: [{ label: 'Home Group', link: '/group' }]
});
