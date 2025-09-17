/**
 * @type: block
 * name: core.block.tabContainer
 * title: Tab Container
 * keywords: container
 * container: true
 * experiment: true
 */

import {
  connectItemView,
  connectSubject,
  createBlock
} from '@metafox/framework';
import { UIBlockViewProps } from '@metafox/ui';
import Base from './Base';

const Enhance = connectSubject(connectItemView(Base, () => {}));

export default createBlock<UIBlockViewProps>({
  extendBlock: Enhance,
  name: 'core.block.tabContainer',
  defaults: {
    title: 'Tab Container',
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
