/**
 * @type: itemView
 * name: sticker.itemView.mainCard
 * chunkName: sticker
 */

import { connectItemView } from '@metafox/video/hocs/connectVideoItem';
import ItemView from './ItemView';

export default connectItemView(ItemView, () => {});
