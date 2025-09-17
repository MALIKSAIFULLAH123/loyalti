/**
 * @type: block
 * name: core.block.AdminWarningBanners
 * title: AdminCP - WarningBanner
 * bundle: admincp
 * experiment: true
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  name: 'core.block.AdminWarningBanners'
});
