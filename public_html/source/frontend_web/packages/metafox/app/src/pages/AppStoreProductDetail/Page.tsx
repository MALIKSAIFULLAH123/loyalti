/**
 * @type: route
 * name: core.admincp.app.store.show
 * path: /app/store/product/:id
 * chunkName: pages.admincp
 * bundle: admincp
 */
import { createViewItemPage } from '@metafox/framework';

export default createViewItemPage({
  appName: 'app',
  resourceName: 'app_store_product',
  pageName: 'core.admincp.app.store.show'
});
