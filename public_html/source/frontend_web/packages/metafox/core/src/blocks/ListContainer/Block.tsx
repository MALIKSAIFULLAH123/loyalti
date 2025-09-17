/**
 * @type: block
 * name: core.block.listContainer
 * title: List Container
 * keywords: container
 * container: true
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import { UIBlockViewProps } from '@metafox/ui';
import Base from './Base';

export default createBlock<UIBlockViewProps>({
  extendBlock: Base,
  name: 'core.block.listContainer',
  defaults: {
    title: 'List Container',
    emptyPage: 'core.block.no_content'
  }
});
