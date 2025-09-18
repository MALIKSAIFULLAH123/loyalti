/**
 * @type: block
 * name: pages.block.pageProfileHeader
 * title: Page Profile Header
 * keywords: page, profile
 * description: Display large page header on page's profile page.
 * thumbnail:
 */
import { connectSubject, createBlock } from '@metafox/framework';
import { actionCreators, connectItemView } from '../../hocs/connectPageDetail';
import Base, { Props } from './Base';

const EnhanceBlock = connectSubject(connectItemView(Base, actionCreators));

export default createBlock<Props>({
  name: 'PageProfileHeaderBlock',
  extendBlock: EnhanceBlock
});
