/**
 * @type: itemView
 * name: story.itemView.storyViewer
 * chunkName: story
 */
import { connectItemView } from '@metafox/framework';
import ItemView from './ItemView';

export default connectItemView(ItemView, () => {});
