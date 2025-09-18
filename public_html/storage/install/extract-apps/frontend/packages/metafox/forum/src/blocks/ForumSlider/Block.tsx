/**
 * @type: block
 * name: forum.block.forumSliderBlock
 * title: Forum
 * keywords: forum
 * description:
 * thumbnail:
 * experiment: true
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  name: 'forums',
  defaults: {
    gridLayout: 'Forum - Slider',
    itemView: 'forum.itemView.mainCard',
    dataSource: {
      apiUrl: '/forum'
    }
  }
});
