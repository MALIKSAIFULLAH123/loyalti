/**
 * @type: block
 * name: livevideo.block.videoView
 * title: Live Video Detail
 * keywords: livestreaming
 * description: Display live video detail
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
