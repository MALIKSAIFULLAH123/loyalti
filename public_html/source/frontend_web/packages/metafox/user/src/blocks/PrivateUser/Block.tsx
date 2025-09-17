/**
 * @type: block
 * name: user.block.privateUser
 */
import {
  connectItemView,
  connectSubject,
  createBlock
} from '@metafox/framework';
import Base, { Props } from './Base';

const Enhance = connectSubject(connectItemView(Base, () => {}));

export default createBlock<Props>({
  extendBlock: Enhance
});
