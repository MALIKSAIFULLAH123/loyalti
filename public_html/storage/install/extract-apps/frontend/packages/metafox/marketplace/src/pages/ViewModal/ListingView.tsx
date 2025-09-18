/**
 * @type: modalRoute
 * name: marketplace.viewModal
 * path: /marketplace/:id
 * bundle: web
 */
import { createViewItemModal } from '@metafox/framework';

export default createViewItemModal({
  appName: 'marketplace',
  resourceName: 'marketplace',
  pageName: 'marketplace.viewModal',
  component: 'marketplace.dialog.listingDetailView'
});
