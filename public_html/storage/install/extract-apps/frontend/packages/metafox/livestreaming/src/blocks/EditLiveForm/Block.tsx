/**
 * @type: block
 * name: livestreaming.block.editLive
 * experiment: true
 */
import { connectSubject, createBlock } from '@metafox/framework';
import Base from './Base';
import {
  connectItemView,
  actionCreators
} from '../../hocs/connectLivestreamItem';

const Enhancer = connectSubject(connectItemView(Base, actionCreators));

export default createBlock<any>({
  extendBlock: Enhancer,
  defaults: {
    blockLayout: 'Blocker'
  }
});
