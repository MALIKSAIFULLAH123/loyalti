/**
 * @type: block
 * name: group.settings.info
 * title: Group Setting Info
 * keywords: group
 * description: Group Setting Info
 * thumbnail:
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
  data: state._actions.group.group.getGroupInfoSettings,
  title: 'group_app_info'
}))(connectSubject(BlockEditInfoItemInfo));

export default createBlock<any>({
  extendBlock: Enhancer,
  defaults: {
    blockLayout: 'Edit Info List',
    appName: APP_GROUP,
    resourceName: APP_GROUP
  }
});
