/**
 * @type: block
 * name: feed.block.feedView
 * title: Feed Detail
 * keywords: feed
 * description: Display feed detail
 * experiment: true
 */

import {
  connectItemView,
  connectSubject,
  createBlock
} from '@metafox/framework';
import Base, { Props } from './Base';

const Enhance = connectSubject(connectItemView(Base, () => {}));

export default createBlock<Props>({
  extendBlock: Enhance,
  overrides: {},
  defaults: {}
});
