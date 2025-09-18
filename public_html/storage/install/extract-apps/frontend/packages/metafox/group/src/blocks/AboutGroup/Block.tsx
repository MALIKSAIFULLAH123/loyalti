/**
 * @type: block
 * name: group.settings.about
 * title: Group Setting About
 * keywords: group
 * description: Group Setting About
 * thumbnail:
 * experiment: true
 */
import {
  connect,
  connectSubject,
  createBlock,
  GlobalState
} from '@metafox/framework';
import { APP_GROUP } from '@metafox/group/constant';
import { BlockEditInfoItemInfo } from '@metafox/ui';

const Enhancer = connect((state: GlobalState) => ({
  data: state._actions.group.group.getGroupAboutSettings,
  title: 'about_group'
}))(connectSubject(BlockEditInfoItemInfo));

export default createBlock<any>({
  extendBlock: Enhancer,
  defaults: {
    blockLayout: 'Edit Info List',
    appName: APP_GROUP,
    resourceName: APP_GROUP
  }
});
