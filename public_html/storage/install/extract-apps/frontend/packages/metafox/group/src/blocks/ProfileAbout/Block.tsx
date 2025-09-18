/**
 * @type: block
 * name: group.block.groupProfileAbout
 * title: Group About Summary
 * keywords: group, profile
 * description: Summary group information in profile page.
 * thumbnail:
 */
import { createBlock } from '@metafox/framework';
import Base, { Props } from './Base';

export default createBlock<Props>({
  name: 'GroupProfileAbout',
  extendBlock: Base
});
