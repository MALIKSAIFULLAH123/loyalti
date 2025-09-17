/**
 * @type: block
 * name: core.block.sidebarAppMenuSetting
 * keywords: sidebar
 * title: App Menu Setting
 * chunkName: sidebar
 */
import {
  connectItemView,
  connectSubject,
  createBlock
} from '@metafox/framework';
import Base, { Props } from './Base';

const Enhance = connectSubject(connectItemView(Base, () => {}));

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    title: 'Sidebar Menu Settings',
    menuName: 'sidebarMenuSetting',
    blockLayout: 'sidebar app menu'
  }
});
