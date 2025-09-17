import { connect, GlobalState } from '@metafox/framework';
import { get } from 'lodash';
import Base from './SuggestionList';

const mapStateToProps = (state: GlobalState, { text, identity }: any) => {
  const item = get(state, identity);

  return { ...state.friend.suggestions[`:${text}`], item_id: item?.id } || {};
};

export default connect(mapStateToProps)(Base);
