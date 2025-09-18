/**
 * @type: block
 * name: advertise.block.banner
 * title: Banner advertise
 * keywords: advertise, banner
 * description: Display advertise banner
 * thumbnail:
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  name: 'AdvertiseBannerBlock',

  extendBlock: Base,
  defaults: {
    title: 'Advertise Banner',
    blockLayout: 'Advertise Banner'
  }
});
