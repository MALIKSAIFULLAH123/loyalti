/**
 * @type: ui
 * name: media.ui.viewBlockMobile
 */
import Base from './Base';
import connectPhotoSet from '@metafox/photo/hocs/connectPhotoSet';
import { connectItemView } from '@metafox/framework';

export default connectItemView(connectPhotoSet(Base), () => {});
