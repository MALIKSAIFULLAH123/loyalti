/**
 * @type: block
 * name: core.block.multipleListingContainer
 * title: Multiple List Container
 * keywords: container
 * experiment: true
 * container: true
 */

import { createBlock } from '@metafox/framework';
import { UIBlockViewProps } from '@metafox/ui';
import Base from './Base';

export default createBlock<UIBlockViewProps>({
  extendBlock: Base,
  name: 'core.block.multipleListingContainer',
  defaults: {
    title: 'List Container'
  }
});
