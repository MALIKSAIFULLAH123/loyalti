/**
 * @type: block
 * name: user.block.detailsAbout
 * title: User Profile Detail About
 * keywords: user, profile
 * description: Display user information in profile page.
 * thumbnail:
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  name: 'UserProfileHeaderBlock',
  extendBlock: Base
});
