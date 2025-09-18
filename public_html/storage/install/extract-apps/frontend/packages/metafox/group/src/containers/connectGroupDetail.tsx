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
    profileMenu: state._resourceMenus.group.group.profileMenu,
    profileActionMenu: state._resourceMenus.group.group.profileActionMenu
  };
}

export const connector = connect(mapStateToProps);

export default function connectUserDetail(BaseView: React.FC<any>) {
  return connector(withUserDetail(BaseView));
}
