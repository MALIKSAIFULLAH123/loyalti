/**
 * @type: block
 * name: sevent.block.mobileMenu
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
        name: 'core.block.sidebarAppMenu',
        props: {
          menuName: 'sidebarMenu',
          blockLayout: 'sidebar app menu'
        }
      },
      {
        name: 'core.block.sidebarAppMenu',
        props: {
          menuName: 'sidebarMyMenu',
          blockLayout: 'sidebar app menu'
        }
      }
    ]
  }
});
