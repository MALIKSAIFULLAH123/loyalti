/**
 * @type: block
 * name: pages.settings.permission
 * title: Pages Permission Settings
 * keywords: page
 * description: Page Permission Settings
 * thumbnail:
 * experiment: true
 */
import { createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock<any>({
  extendBlock: Base,
  defaults: {
    title: 'permissions',
    blockLayout: 'Edit Info List'
  }
});
