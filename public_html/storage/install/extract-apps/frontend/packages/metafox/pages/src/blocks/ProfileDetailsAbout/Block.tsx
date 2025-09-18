/**
 * @type: block
 * name: page.block.detailsAbout
 * title: Page Profile Info
 * keywords: page, profile
 * description: Display user information in profile page.
 * thumbnail:
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  name: 'UserProfileDetailAboutBlock',
  extendBlock: Base
});
