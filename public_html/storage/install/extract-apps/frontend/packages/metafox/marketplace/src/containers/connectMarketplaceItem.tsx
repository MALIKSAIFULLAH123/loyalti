import { connect, withItemView } from '@metafox/framework';
import { GlobalState } from '@metafox/framework/Manager';
import { get } from 'lodash';
import itemActions from '../actions/marketplaceItemActions';

export const enhacer = withItemView({}, itemActions);

function mapStateToProps(state: GlobalState, ownProps: any) {
  const item = get(state, ownProps.identity);

  if (!item) {
    return {};
  }

  return {
    item,
    user: get(state, item.user),
    categories: item.categories
      ? item.categories.map((x: string) => get(state, x)).filter(Boolean)
      : [],
    tags: item.tags
      ? item.tags.map((x: string) => get(state, x)).filter(Boolean)
      : []
  };
}

export const connector = connect(mapStateToProps);

export default function connectItem(BaseView: React.FC<any>) {
  return connector(enhacer(BaseView));
}
