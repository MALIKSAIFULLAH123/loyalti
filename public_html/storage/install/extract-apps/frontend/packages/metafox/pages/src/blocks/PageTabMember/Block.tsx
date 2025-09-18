/**
 * @type: block
 * name: pages.member.tabContainer
 * title: Page Member Tab
 * keywords: page
 * description: Page Member
 * thumbnail:
 * experiment: true
 */
import { BlockViewProps, createBlock } from '@metafox/framework';

export default createBlock<BlockViewProps>({
  extendBlock: 'core.block.tabContainer',
  defaults: {
    blockLayout: 'Main Form'
  },
  overrides: {}
});
