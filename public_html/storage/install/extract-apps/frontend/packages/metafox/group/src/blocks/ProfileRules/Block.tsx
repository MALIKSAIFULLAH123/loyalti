/**
 * @type: block
 * name: group.block.groupProfileRules
 * title: Group Profile Rules
 * keywords: group
 * description: Display group information in profile page.
 * thumbnail:
 */
import { connectSubject, createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

const Enhancer = connectSubject(Base);

export default createBlock<Props>({
  name: 'GroupProfileRules',
  extendBlock: Enhancer
});
