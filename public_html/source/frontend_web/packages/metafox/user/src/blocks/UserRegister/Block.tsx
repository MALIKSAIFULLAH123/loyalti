/**
 * @type: block
 * name: user.register
 * title: Register
 * keywords: user
 */

import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  overrides: {
    blockProps: {
      testid: 'user-register'
    }
  },
  defaults: {
    title: 'Create New Account'
  }
});
