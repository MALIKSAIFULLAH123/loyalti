/**
 * @type: block
 * name: livestreaming.block.livestreamView
 * title: Livestream Detail
 * keywords: livestreaming
 * description: Display livestream detail
 * experiment: true
 */

import { connectSubject, createBlock } from '@metafox/framework';
import {
  actionCreators,
  connectItemView
} from '../../hocs/connectLivestreamItem';
import Base from './Base';

const Enhance = connectSubject(connectItemView(Base, actionCreators));

export default createBlock({
  extendBlock: Enhance
});
