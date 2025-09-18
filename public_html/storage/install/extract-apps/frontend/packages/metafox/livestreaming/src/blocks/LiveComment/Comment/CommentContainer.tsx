/**
 * @type: ui
 * name: CommentItemViewLiveStreaming
 */

import { connectItemView } from '@metafox/framework';
import { default as actionCreators } from '@metafox/livestreaming/actions/commentItemActions';
import Comment from './Comment';

export default connectItemView(Comment, actionCreators, { extra_data: true });
