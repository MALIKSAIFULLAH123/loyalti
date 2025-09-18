/**
 * @type: route
 * name: sevent.ticket.browse
 * path: /sevent/ticket/my
 * chunkName: pages.sevent
 * bundle: web
 */
import { createBrowseItemPage } from '@metafox/framework';

export default createBrowseItemPage({
  appName: 'sevent',
  resourceName: 'sevent_user_ticket',
  pageName: 'sevent.ticket.browse',
  defaultTab: 'sevent_ticket_my',
  categoryName: 'sevent_category',
  paramCreator: prev => ({
    tab: prev.tab?.replace(/-/g, '_'),
    view: prev.tab?.replace(/-/g, '_')
  })
});
