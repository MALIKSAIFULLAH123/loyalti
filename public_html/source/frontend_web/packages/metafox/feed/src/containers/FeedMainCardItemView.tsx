/**
 * @type: itemView
 * name: feed.itemView.mainCard
 * chunkName: feed
 */

import { connect, GlobalState } from '@metafox/framework';
import { get } from 'lodash';
import ItemView from '../components/FeedItemView';
import withFeedItem from './withFeedItem';

const mapStateToProps = (state: GlobalState, ownProps: any) => {
  const item = get(state, ownProps.identity);

  if (!item) return {};

  return {
    item,
    user: item.user ? get(state, item.user) : undefined,
    parent_user: item.parent_user ? get(state, item.parent_user) : undefined,
    tagged_friends: item.tagged_friends
      ? item.tagged_friends.map((x: string) => get(state, x)).filter(Boolean)
      : undefined,
    embed_object: item.embed_object ? get(state, item.embed_object) : undefined
  };
};

export default connect(mapStateToProps)(withFeedItem(ItemView));
