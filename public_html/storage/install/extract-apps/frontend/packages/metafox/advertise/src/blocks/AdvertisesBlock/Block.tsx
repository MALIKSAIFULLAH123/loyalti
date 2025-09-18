/**
 * @type: block
 * name: advertise.block.advertisesBlock
 * title: Advertises Block
 * keywords: advertise
 * description: Display advertises as html or banner
 * thumbnail:
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  name: 'AdvertisesBlock',

  extendBlock: Base,
  defaults: {
    title: 'Advertises Block',
    blockLayout: 'Advertise - All Placement'
  },
  custom: {
    description: {
      component: 'Typo',
      name: 'description',
      sx: { m: 0 },
      tagName: 'body2',
      color: 'text.secondary',
      plainText:
        // eslint-disable-next-line max-len
        'For example: <br/> Choose "Advertise - All Placement": to show ads at all placement full width <br/> Choose "Advertise - Top - Profile Page": to show ads at the placement Header or Top of Profile Page... <br/>'
    },
    placement: {
      component: 'SelectPlacement',
      name: 'placement_id',
      variant: 'outlined',
      label: 'Placement'
    }
  }
});
