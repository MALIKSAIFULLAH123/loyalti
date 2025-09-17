import { GlobalState } from '@metafox/framework';
import { get } from 'lodash';

export default function mapStateToProps(state: GlobalState, ownProps: any) {
  const item = get(state, ownProps.identity);

  if (!item) {
    return {};
  }

  return {
    item,
    user: get(state, item.user)
  };
}
