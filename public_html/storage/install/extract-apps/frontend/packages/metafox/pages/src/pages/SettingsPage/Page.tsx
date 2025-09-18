/**
 * @type: route
 * name: pages.settings
 * path: /page/settings/:id(\d+)/:tab?
 * chunkName: pages.page
 * bundle: web
 * experiment: true
 */
import { createMultiTabPage } from '@metafox/framework';

export default createMultiTabPage({
  appName: 'page',
  resourceName: 'page',
  pageName: 'pages.settings',
  showWhen: ['truthy', 'item.extra.can_edit'],
  defaultTab: 'info',
  tabs: {
    info: 'pages.settings.info',
    about: 'pages.settings.about',
    permissions: 'pages.settings.permission',
    menu: 'pages.settings.menu',
    chat: 'core.block.comingSoon',
    'schedule-post': 'pages.block.schedule-posts'
  }
});
