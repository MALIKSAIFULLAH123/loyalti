/**
 * @type: block
 * name: advertise.block.html
 * title: Advertise HTML
 * keywords: advertise, html
 * description: Display advertise html
 * thumbnail:
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  name: 'AdvertiseBannerBlock',

  extendBlock: Base,
  defaults: {
    title: 'Advertise HTML',
    blockLayout: 'Advertise Html'
  }
});
