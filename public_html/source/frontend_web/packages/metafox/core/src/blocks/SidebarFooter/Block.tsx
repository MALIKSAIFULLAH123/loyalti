/**
 * @type: block
 * name: core.block.sidebar.footer
 * title: Footer Menu
 * keywords: sidebar
 * chunkName: sidebar
 */
import { createBlock, GlobalState } from '@metafox/framework';
import { connect } from 'react-redux';
import Base, { Props } from './Base';

const Enhance = connect((state: GlobalState) => state.core.ui)(Base);

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    blockProps: {
      testid: 'sidebarFooterBlock',
      variant: 'plained',
      paddingLeft: '2',
      paddingRight: '2'
    }
  }
});
