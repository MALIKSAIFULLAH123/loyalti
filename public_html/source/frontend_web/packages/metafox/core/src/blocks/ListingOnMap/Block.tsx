/**
 * @type: block
 * name: core.block.listingMap
 * title: Main map
 * keywords: map listing
 * description: Display map.
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Base';

export default createBlock<ListViewBlockProps>({
  extendBlock: Base,
  defaults: {
    fixedView: true
  },
  custom: {
    fixedView: {
      name: 'fixedView',
      component: 'Checkbox',
      label: 'Layout fixed',
      fullWidth: true,
      checkedValue: true
    }
  }
});
