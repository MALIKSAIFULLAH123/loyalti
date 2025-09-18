/**
 * @type: service
 * name: CommentItemView
 */

import { connectItemView } from '@metafox/framework';
import { default as actionCreators } from '../actions/commentItemActions';
import Comment from '../components/Comment/Comment';

export default connectItemView(Comment, actionCreators, { extra_data: true });
