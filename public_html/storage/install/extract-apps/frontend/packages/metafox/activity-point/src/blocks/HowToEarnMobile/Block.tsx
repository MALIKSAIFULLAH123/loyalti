/**
 * @type: block
 * name: activity-point.block.howToEarnMobile
 * keyword: activity point
 * title: Activity Points
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    blockProps: {
      headerStyle: {
        sx: {
          pl: 0
        }
      }
    }
  }
});
