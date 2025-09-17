import { withItemView } from '@metafox/framework';
import itemActions from '../actions/feedItemActions';

const itemState = () => {
  return {
    menuOpened: false,
    commentOpened: true,
    commentFocused: false
  };
};

export default withItemView(itemState, itemActions);
