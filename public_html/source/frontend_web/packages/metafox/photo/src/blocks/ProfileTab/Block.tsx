/**
 * @type: block
 * name: photo.block.profileTab
 * title: Photo Profile Tab
 * keywords: container
 * container: true
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import { UIBlockViewProps } from '@metafox/ui';
import Base from './Base';

export default createBlock<UIBlockViewProps>({
  extendBlock: Base,
  name: 'photo.block.profileTab',
  defaults: {
    title: 'photos',
    blockProps: {
      blockStyle: {
        borderRadius: 'base'
      }
    }
  },
  overrides: {
    blockProps: {
      noFooter: true
    }
  },
  custom: {
    hasSearchBox: {
      name: 'hasSearchBox',
      component: 'Checkbox',
      label: 'Has Search Box?',
      margin: 'normal'
    }
  }
});
