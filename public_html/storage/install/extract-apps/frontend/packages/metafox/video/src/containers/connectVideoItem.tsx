import { connect, withItemView } from '@metafox/framework';
import { GlobalState } from '@metafox/framework/Manager';
import { get } from 'lodash';

export const withVideoItem = withItemView({}, () => ({}));

function mapStateToProps(state: GlobalState, ownProps: any) {
  const item = get(state, ownProps.identity);

  if (!item) {
    return {};
  }

  return {
    item,
    user: get(state, item.user)
  };
}

export const connector = connect(mapStateToProps);

export default function connectVideoItem(BaseView: React.FC<any>) {
  return connector(withVideoItem(BaseView));
}
