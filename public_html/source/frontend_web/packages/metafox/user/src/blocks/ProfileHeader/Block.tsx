/**
 * @type: block
 * name: user.block.userProfileHeader
 * title: User Profile Header
 * keywords: user, profile
 * description: Display large user header on user profile page.
 * thumbnail:
 */
import { connectSubject, createBlock } from '@metafox/framework';
import { actionCreators, connectItemView } from '../../hocs/connectUserDetail';
import Base, { Props } from './Base';

const EnhanceBlock = connectSubject(connectItemView(Base, actionCreators));

export default createBlock<Props>({
  name: 'UserProfileHeaderBlock',
  extendBlock: EnhanceBlock
});
