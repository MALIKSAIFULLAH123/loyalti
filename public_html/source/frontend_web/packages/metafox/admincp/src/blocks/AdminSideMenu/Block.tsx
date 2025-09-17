/**
 * @type: block
 * name: core.block.AdminSideMenu
 * title: AdminCP - Side Menu
 * bundle: admincp
 * experiment: true
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  name: 'core.block.AdminSideMenu'
});
