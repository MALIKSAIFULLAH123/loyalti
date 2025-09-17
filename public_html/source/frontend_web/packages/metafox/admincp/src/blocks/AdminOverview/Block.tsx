/**
 * @type: block
 * name: core.block.AdminOverview
 * title: AdminCP - Overview Info
 * bundle: admincp
 */
import { createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock({
  extendBlock: Base,
  defaults: {
    title: 'Overview'
  }
});
