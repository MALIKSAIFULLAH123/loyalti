/**
 * @type: block
 * name: core.block.sidebarPrimaryMenu
 * title: Main Menu
 * keywords: sidebar
 * description:
 * thumbnail:
 * chunkName: sidebarHome
 */
import { createBlock, GlobalState } from '@metafox/framework';
import { connect } from 'react-redux';
import Base, { Props } from './Base';

const Enhance = connect((state: GlobalState) => state.core.uiConfig)(Base);

export default createBlock<Props>({
  extendBlock: Enhance,
  overrides: {
    blockProps: {
      testid: 'sidebarMenu'
    }
  },
  defaults: {
    displayLimit: 8,
    title: 'Sidebar Menu'
  },
  custom: {
    displayLimit: {
      name: 'displayLimit',
      component: 'Text',
      label: 'Limit Item',
      fullWidth: true,
      margin: 'normal',
      variant: 'outlined',
      placeholder: '8'
    }
  }
});
