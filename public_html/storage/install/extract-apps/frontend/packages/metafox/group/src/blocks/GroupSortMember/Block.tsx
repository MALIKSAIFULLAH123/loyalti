/**
 * @type: block
 * name: group.block.groupSortMemberBlock
 * title: Group Sort Member
 * keywords: group member
 * experiment: true
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  extendBlock: Base,
  defaults: {
    moduleName: 'group',
    resourceName: 'group',
    actionName: 'searchMember'
  }
});
