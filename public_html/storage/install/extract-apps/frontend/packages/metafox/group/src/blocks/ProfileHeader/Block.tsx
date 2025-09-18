/**
 * @type: block
 * name: group.block.groupProfileHeader
 * title: Group Profile Header
 * keywords: group, profile
 * description: Display large header on group's profile.
 * thumbnail:
 */
import {
  connectProfileView as connectItemView,
  connectSubject,
  createBlock
} from '@metafox/framework';
import actionCreators from '../../actions/groupItemActions';
import Base, { Props } from './Base';

const EnhanceBlock = connectSubject(connectItemView(Base, actionCreators));

export default createBlock<Props>({
  name: 'GroupProfileHeaderBlock',
  extendBlock: EnhanceBlock
});
