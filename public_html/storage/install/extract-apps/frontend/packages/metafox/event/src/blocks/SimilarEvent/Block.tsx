/**
 * @type: block
 * name: event.block.similarEventBlock
 * title: Similar Events
 * keywords: event
 * description: Display similar events.
 * experiment: true
 */
import {
  connectItemView,
  connectSubject,
  createBlock,
  ListViewBlockProps
} from '@metafox/framework';
import Base from './Base';

const Enhance = connectSubject(connectItemView(Base, () => {}));

const SimilarEventBlock = createBlock<ListViewBlockProps>({
  extendBlock: Enhance,
  defaults: {
    title: 'Similar Event'
  }
});

export default SimilarEventBlock;
