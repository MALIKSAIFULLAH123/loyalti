/**
 * @type: block
 * name: story.block.mainViewHome
 * title: Home MainView
 * keywords: main
 * description:
 * thumbnail:
 * experiment: true
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {}
});
