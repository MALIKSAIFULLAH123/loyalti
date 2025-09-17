/**
 * @type: block
 * name: user.block.verifyAccount
 * title: VerifyAccount Form
 * keywords: general
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  name: 'VerifyAccount',
  defaults: {
    blockLayout: 'Main Form'
  },
  overrides: {
    title: 'Main Form'
  }
});
