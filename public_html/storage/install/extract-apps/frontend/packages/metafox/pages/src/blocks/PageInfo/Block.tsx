/**
 * @type: block
 * name: pages.settings.info
 * title: Pages Setting Info
 * keywords: page
 * description: Pages Setting Info
 * thumbnail:
 */
import {
  connect,
  connectSubject,
  createBlock,
  GlobalState
} from '@metafox/framework';
import { APP_PAGE } from '@metafox/pages/constant';
import { BlockEditInfoItemInfo } from '@metafox/ui';

const Enhancer = connect((state: GlobalState) => ({
  data: state._actions.page.page.getInfoSettings,
  title: 'page_info'
}))(connectSubject(BlockEditInfoItemInfo));

export default createBlock<any>({
  extendBlock: Enhancer,
  defaults: {
    blockLayout: 'Edit Info List',
    appName: APP_PAGE,
    resourceName: APP_PAGE
  }
});
