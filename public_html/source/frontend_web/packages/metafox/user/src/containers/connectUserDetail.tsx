import { connect, withItemView } from '@metafox/framework';
import { GlobalState } from '@metafox/framework/Manager';
import { get } from 'lodash';

export const withUserDetail = withItemView({}, () => ({}));

function mapStateToProps(state: GlobalState, ownProps: any) {
  const item = get(state, ownProps.identity);

  if (!item) {
    return {};
  }

  return {
    item,
    user: get(state, item.user),
    profileMenu: state._resourceMenus.user.user.profileMenu,
    profileActionMenu: state._resourceMenus.user.user.profileActionMenu
  };
}

export const connector = connect(mapStateToProps);

export default function connectUserDetail(BaseView: React.FC<any>) {
  return connector(withUserDetail(BaseView));
}
