import { withItemView } from '@metafox/framework';
import pagesItemAction from '../actions/pagesItemActions';

const withPagesItem = withItemView({}, pagesItemAction);

export default withPagesItem;
