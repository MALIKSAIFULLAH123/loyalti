/**
 * @type: block
 * name: admincp.block.searchSettingForm
 * title: App - AdminCP - Form
 * experiment: true
 * bundle: admincp
 */

import { createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock({
  extendBlock: Base,
  defaults: {
    title: 'App Search Setting Form'
  }
});
