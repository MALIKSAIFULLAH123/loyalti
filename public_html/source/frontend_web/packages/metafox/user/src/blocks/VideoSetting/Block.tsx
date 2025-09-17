/**
 * @type: block
 * name: setting.block.video
 * title: User Settings - Video
 * keywords: user, settings
 * experiment: true
 */
import { connect, createBlock, GlobalState } from '@metafox/framework';
import Base from './Base';

const Enhancer = connect((state: GlobalState) => ({
  dataSource: state._actions.user.user.getVideoSettings
}))(Base);

export default createBlock<any>({
  extendBlock: Enhancer,
  defaults: {
    testid: 'setting.block.video',
    title: 'account_settings',
    blockLayout: 'Account Setting'
  }
});
