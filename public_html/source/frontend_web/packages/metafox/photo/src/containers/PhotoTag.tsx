import { connect, GlobalState } from '@metafox/framework';
import { get } from 'lodash';
import Base from '../components/PhotoTag';

const mapStateToProps = (state: GlobalState, { identity }: any) => {
  const item = get(state, identity);

  if (!item) {
    return {};
  }

  return {
    item,
    user: item.user ? get(state, item.user) : undefined
  };
};
export default connect(mapStateToProps)(Base);
