/**
 * @type: block
 * name: core.block.AdminDetailDataListing
 * title: Admin DetailDataListing
 * keywords: admincp
 * admincp: true
 */

import { createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock({
  extendBlock: Base,
  defaults: {
    title: 'Data Listing'
  }
});
