/**
 * @type: block
 * name: setting.block.schedule-posts
 * title: User Setting - Schedule Posts
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
    title: 'scheduled_posts',
    contentType: 'feed',
    itemView: 'user.itemView.schedulePosts',
    blockLayout: 'Manage Scheudle Posts',
    gridLayout: 'User - PendingPost - Main Card'
  }
});
