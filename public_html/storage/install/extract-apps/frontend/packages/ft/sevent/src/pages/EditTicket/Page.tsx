/**
 * @type: route
 * name: sevent_ticket.edit
 * path: /sevent/ticket/edit/:id,/sevent/ticket/add
 * chunkName: pages.sevent
 * bundle: web
 */

import { createEditingPage } from '@metafox/framework';

export default createEditingPage({
  appName: 'sevent',
  resourceName: 'sevent_ticket',
  pageName: 'sevent_ticket.edit'
});
