/**
 * @type: block
 * name: feed.block.statusComposer
 * title: Feed Composer
 * keywords: feed
 * description:
 * thumbnail:
 * experiment: true
 */

import {
  connectItemView,
  connectSubject,
  createBlock
} from '@metafox/framework';
import Base, { Props } from './Base';

const EnhanceBlock = connectSubject(connectItemView(Base, () => {}));

export default createBlock<Props>({
  extendBlock: EnhanceBlock,
  name: 'StatusComposer',
  testid: 'StatusComposer',
  defaults: {
    variant: 'default',
    title: 'Status Composer',
    blockLayout: 'Status Composer'
  },
  custom: {
    variant: {
      name: 'variant',
      component: 'Select',
      label: 'Variant',
      fullWidth: true,
      margin: 'normal',
      variant: 'outlined',
      options: [
        { label: 'Default', value: 'default' },
        { label: 'Expanded', value: 'expanded' }
      ]
    }
  }
});
