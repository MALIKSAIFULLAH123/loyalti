/**
 * @type: block
 * name: core.block.AdminItemStatistic
 * title: AdminCP - Content Statistics
 * bundle: admincp
 * admincp: true
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    title: 'Item Statistic',
    blockProps: {
      blockStyle: {
        sx: {
          mt: 2,
          mr: 0,
          ml: 0,
          mb: 0
        }
      }
    }
  }
});
