/**
 * @type: service
 * name: ReplyItemView
 */

import { connectItemView } from '@metafox/framework';
import { default as actionCreators } from '../actions/commentItemActions';
import Base from '../components/Comment/Reply';

export default connectItemView(Base, actionCreators, { extra_data: true });
