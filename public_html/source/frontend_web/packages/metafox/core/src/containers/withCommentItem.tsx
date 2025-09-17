import { withItemView } from '@metafox/framework';

const itemState = props => {
  return {
    menuOpened: false,
    commentOpened: props?.hideListComment ? false : true,
    commentFocused: false
  };
};

export default withItemView(itemState, () => ({}));
