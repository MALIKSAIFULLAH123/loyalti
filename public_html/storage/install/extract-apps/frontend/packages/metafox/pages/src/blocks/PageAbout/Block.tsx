/**
 * @type: block
 * name: pages.settings.about
 * title: Page Setting About
 * keywords: page
 * description: Page Setting About
 * thumbnail:
 * experiment: true
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
  data: state._actions.page.page.getAboutSettings,
  title: 'about_page'
}))(connectSubject(BlockEditInfoItemInfo));

export default createBlock<any>({
  extendBlock: Enhancer,
  defaults: {
    blockLayout: 'Edit Info List',
    appName: APP_PAGE,
    resourceName: APP_PAGE
  }
});
