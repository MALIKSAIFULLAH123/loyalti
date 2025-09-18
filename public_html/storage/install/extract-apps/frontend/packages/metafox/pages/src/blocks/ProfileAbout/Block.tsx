/**
 * @type: block
 * name: pages.block.pageProfileAbout
 * title: Page About
 * keywords: page
 * description: Display page information in profile page.
 * thumbnail:
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  name: 'PageProfileHeaderBlock',
  extendBlock: Base
});
