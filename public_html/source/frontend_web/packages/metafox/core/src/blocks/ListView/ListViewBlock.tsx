/**
 * @type: ui
 * name: core.block.listview
 * chunkName: listview
 */
import { withPagination } from '@metafox/framework';
import ListViewContainer from './ListViewContainer';

export default withPagination(ListViewContainer);
