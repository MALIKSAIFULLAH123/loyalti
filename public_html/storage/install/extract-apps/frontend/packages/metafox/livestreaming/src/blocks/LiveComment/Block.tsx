/**
 * @type: block
 * name: livestreaming.block.commentLive
 * title: livestream detail comment
 * keywords: livestreaming
 * description: Display livestream detail
 * experiment: true
 */

import { connectSubject, createBlock } from '@metafox/framework';
import Base from './Base';

const Enhance = connectSubject(Base);

export default createBlock({
  extendBlock: Enhance,
  defaults: {
    blockLayout: 'LiveStreaming Comment'
  }
});
