import { SmartDataGridProps, useGlobal } from '@metafox/framework';
import { Alert, Box } from '@mui/material';
import React from 'react';
import DataGridBase from './DataGridBase';
import OptionCell from './OptionCell';
import { isString } from 'lodash';

/**
 * Keep data grid config cache time to life milliseconds.
 */
const GRID_CONFIG_TTL = 300000;

type Props = Omit<
  SmartDataGridProps,
  'fetchAction' | 'getRowId' | 'batchActionMenu' | 'itemActionMenu' | 'columns'
> & {
  appName: string;
  resourceName: string;
};

function RemoteDataGrid({
  config,
  appName,
  resourceName,
  gridName,
  ...rest
}: Props) {
  const { i18n, jsxBackend } = useGlobal();

  const columns: any[] = React.useMemo(() => {
    const columns = config.columns.map(column => {
      return {
        ...column,
        renderCell: jsxBackend.get(
          `dataGrid.cell.${column.renderCell ?? 'BasicCell'}`
        ),
        renderHeader: jsxBackend.get('dataGrid.header.Basic')
      };
    });
    // dataGrid.cell.HeaderBasicCell

    if (config.sortable) {
      columns.unshift({
        field: 'sortable',
        width: 32,
        minWidth: 32,
        renderHeader: undefined,
        renderCell: jsxBackend.get('dataGrid.cell.SortIconCell')
      });
    }

    if (config.checkboxSelection) {
      columns.unshift({
        field: 'checkbox',
        width: 56,
        minWidth: 56,
        renderCell: jsxBackend.get('dataGrid.cell.Checkbox'),
        renderHeader: jsxBackend.get('dataGrid.header.Checkbox')
      });
    }

    if (
      config.itemActionMenu?.items.length &&
      !config?.manualColumnItemActionMenu
    ) {
      columns.push({
        field: 'options',
        width: 120,
        minWidth: 100,
        align: 'center',
        headerName: i18n.formatMessage({ id: 'options' }),
        renderCell: OptionCell,
        renderHeader: undefined
      });
    }

    return columns;

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [appName, resourceName, gridName]);

  if (!config) {
    return (
      <Box px={2} py={2} component="div">
        <Alert variant="standard" color="error">
          Oops!, could not find configuration.
        </Alert>
      </Box>
    );
  }

  const cloneConf = { ...config, appName, resourceName };

  return (
    <DataGridBase
      key={`${appName}.${resourceName}.${gridName}`}
      gridName={gridName}
      config={cloneConf}
      columns={columns}
      rows={[]}
      {...rest}
    />
  );
}

function LoadDataGrid(props) {
  const { useFetchDetail, jsxBackend } = useGlobal();
  const {
    gridName: name,
    dataSource,
    loadingComponent = 'ui.admincp.loading',
    errorComponent = 'ui.dataGrid.error',
    forceReload = false,
    configTTL
  } = props;

  const fetchAction = dataSource
    ? dataSource
    : {
        apiUrl: `admincp/core/grid/${name}`
      };

  // how to handle this use cached data
  const [config, loading, error] = useFetchDetail({
    dataSource: fetchAction,
    ttl: configTTL || GRID_CONFIG_TTL,
    cachePrefix: 'datagrid',
    cacheKey: name,
    forceReload
  });

  const LoadingComponent = isString(loadingComponent)
    ? jsxBackend.get(loadingComponent)
    : loadingComponent;

  const ErrorComponent = isString(errorComponent)
    ? jsxBackend.get(errorComponent)
    : errorComponent;

  if (loading) return React.createElement(LoadingComponent, { error, loading });

  if (error) return React.createElement(ErrorComponent, { error });

  if (config?.isHidden) {
    return null;
  }

  return <RemoteDataGrid {...props} config={config} />;
}

function SmartDataGrid(props) {
  const { gridName, resourceName, appName } = props;

  return (
    <LoadDataGrid {...props} key={`${appName}.${resourceName}.${gridName}`} />
  );
}

export default React.memo(
  SmartDataGrid,
  (prev, next) =>
    prev.appName === next.appName &&
    prev.resourceName === next.resourceName &&
    prev.gridName === next.gridName
);
