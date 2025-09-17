/**
 * @type: block
 * name: setting.block.review-posts
 * title: User Setting - Review Posts
 * keywords: user, settings
 * experiment: true
 */
import { createBlock, GlobalState } from '@metafox/framework';
import { connect } from 'react-redux';
import Base, { Props } from './Base';

const Enhance = connect((state: GlobalState) => state.user.profilePrivacy)(
  Base
);

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    title: 'review_posts',
    contentType: 'feed',
    itemView: 'user.itemView.reviewPosts',
    blockLayout: 'App List Reviews Posts',
    gridLayout: 'User - PendingPost - Main Card'
  }
});
