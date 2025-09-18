/**
 * @type: block
 * name: video.block.videoViewMobile
 * title: Video Detail Mobile
 * keywords: video
 * description: Display video detail mobile
 */

import {
  connectSubject,
  createBlock,
  connectItemView
} from '@metafox/framework';
import Base, { Props } from './Base';

const Enhance = connectSubject(connectItemView(Base, () => {}));

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {}
});
