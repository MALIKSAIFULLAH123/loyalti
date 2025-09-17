/**
 * @type: block
 * name: core.block.AdminSiteStatistic
 * title: Admincp - Site Statistics
 * bundle: admincp
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base
});
