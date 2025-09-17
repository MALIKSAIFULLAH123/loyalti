import loadable from '@loadable/component';

export const SmartDataGrid = loadable(
  () =>
    import(
      /* webpackChunkName: "dataGrid"  */
      './SmartDataGrid/DataGrid'
    )
);
