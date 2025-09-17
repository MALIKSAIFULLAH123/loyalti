/**
 * @type: block
 * name: setting.block.items
 * title: User Settings - Sharing Items
 * keywords: user, settings
 * experiment: true
 */
import { createBlock, GlobalState } from '@metafox/framework';
import { connect } from 'react-redux';
import Base, { Props } from './Base';

const Enhance = connect((state: GlobalState) => state.user.sharingItemPrivacy)(
  Base
);

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    title: 'sharing_items',
    blockLayout: 'Account Setting'
  }
});
