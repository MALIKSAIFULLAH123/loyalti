/**
 * @type: block
 * name: core.block.sidebarAppMobile
 * keywords: sidebar
 * title: App Menu Mobile
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    blockProps: {
      testid: 'sidebarMenu',
      title: 'App Menu Mobile'
    },
    contents: [
      {
        name: 'core.block.searchBox'
      },
      {
        name: 'core.block.sidebarAppMenu'
      },
      {
        name: 'core.dividerBlock'
      },
      {
        name: 'core.categoryBlock'
      }
    ]
  }
});
