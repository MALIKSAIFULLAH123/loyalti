/**
 * @type: block
 * name: video.block.videoView
 * title: Video Detail
 * keywords: video
 * description: Display video detail
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
