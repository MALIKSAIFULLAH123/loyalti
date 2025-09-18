import { connect, withItemView } from '@metafox/framework';
import { GlobalState } from '@metafox/framework/Manager';
import { get } from 'lodash';

export const withSongItem = withItemView({}, () => ({}));

function mapStateToProps(state: GlobalState, ownProps: any) {
  const item = get(state, ownProps.identity);

  if (!item) {
    return {};
  }

  const songs = item.songs
    ? item.songs.map(x => get(state, x)).filter(Boolean)
    : [];

  return {
    item,
    songs,
    user: get(state, item.user)
  };
}

export const connector = connect(mapStateToProps);

export default function connectSongItem(BaseView: React.FC<any>) {
  return connector(withSongItem(BaseView));
}
