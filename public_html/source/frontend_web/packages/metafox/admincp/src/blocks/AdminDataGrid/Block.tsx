/**
 * @type: block
 * name: user.block.AdminDataGrid
 * title: AdminCP - DataGrid
 * bundle: admincp
 */

import { createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock({
  extendBlock: Base,
  defaults: {
    title: 'Data Grid'
  },
  custom: {
    appName: {
      name: 'dataGridProps.appName',
      component: 'Text',
      label: 'App Name',
      variant: 'outlined',
      required: true,
      fullWidth: true
    },
    resourceName: {
      name: 'dataGridProps.resourceName',
      component: 'Text',
      label: 'Resource Name',
      variant: 'outlined',
      required: true,
      fullWidth: true
    },
    gridName: {
      name: 'dataGridProps.gridName',
      component: 'Text',
      label: 'Grid Name',
      variant: 'outlined',
      required: true,
      fullWidth: true
    }
  }
});
