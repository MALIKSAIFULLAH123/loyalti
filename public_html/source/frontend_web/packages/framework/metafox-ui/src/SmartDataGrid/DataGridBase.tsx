import {
  DataGridContextShape,
  GridRowData,
  GridRowId,
  IS_ADMINCP,
  SmartDataGridProps,
  useGlobal,
  useLocation
} from '@metafox/framework';
import { escapeRegExp } from '@metafox/utils';
import { Alert, Box, Typography } from '@mui/material';
import React, { useCallback } from 'react';
import { useResizeDetector } from 'react-resize-detector';
import { VariableSizeList } from 'react-window';
import BasicCell from './BasicCell';
import DataGridContext from './DataGridContext';
import GridBanner from './GridBanner';
import GridBody from './GridBody';
import BodyCell from './GridCell';
import Footer from './GridFooter';
import GridHead from './GridHead';
import GridMain from './GridMain';
import GridRow from './GridRow';
import GridSearchForm from './GridSearchForm';
import GridDataStateContext from './GridStateContext';
import GridToolbar from './GridToolbar';
import HeaderBasic from './HeaderBasic';
import Loading from './Loading';
import NoResults from './NoResults';
import useStyles from './styles';
import useListContentHeight from './useListContentHeight';
import Wrapper from './Wrapper';
import DataGridDnD from './DataGridDnD';
import { isEqual } from 'lodash';
import DataGridAdditionalSection from './DataGridAdditionalSection/Base';
import qs from 'query-string';

const DEFAULT_PERPAGE = 20;

const TotalItems = ({ message, total }: { message: string; total: number }) => {
  const { i18n } = useGlobal();

  if (!message || !total) return null;

  return (
    <Box sx={{ display: 'flex', alignItems: 'flex-end', height: '100%' }}>
      <Typography fontWeight={600} variant={'body1'}>
        {i18n.formatMessage(
          { id: message, defaultMessage: message },
          { value: total }
        )}
      </Typography>
    </Box>
  );
};

export default function DataGridBase({
  rows: initRows,
  title,
  columns,
  remoteSearchForm,
  config,
  gridName
}: SmartDataGridProps) {
  const classes = useStyles();
  const {
    useFetchItems,
    useSmartDataGrid,
    dispatch,
    usePageParams,
    usePrevious,
    jsxBackend,
    navigate,
    eventCenter
  } = useGlobal();

  const {
    inlineSearch,
    hideTitle = false,
    pageSize: pageSizeProp,
    rowsPerPage,
    page: prevPage = 1,
    checkboxSelection,
    description,
    searchFormPlacement = 'top',
    hideDescription,
    hideToggleSearch,
    searchForm,
    batchActionMenu,
    gridActionMenu,
    disableSelectionOnClick = true,
    rowsPerPageOptions, // can pass multiple row per page options etc:  [20,50,100]
    searchFormVisible: searchFormVisiblePrev = true,
    dataSource,
    rowHeight = 48,
    headerHeight = 48,
    footerHeight: FOOTER_HEIGHT = 56,
    height,
    idField = 'id',
    dynamicRowHeight = false,
    sortable,
    additionalSection,
    allowRiskParams = true,
    errorComponent = 'ui.dataGrid.error',
    extraData
  } = config;
  const { show_total, total_item_phrase } = extraData || {};

  const { pathname } = useLocation();

  const prevPageSize = pageSizeProp || rowsPerPage || DEFAULT_PERPAGE;

  const ErrorComponent = jsxBackend.get(errorComponent);

  const fetchAction = dataSource ? dataSource : config.actions.viewAll;

  const pageContextParams = usePageParams();
  const prevPageContextParams = usePrevious(pageContextParams);

  // eslint-disable-next-line react-hooks/exhaustive-deps
  const getRowId = React.useCallback(row => row[idField], []);

  const [page, setPage] = React.useState<number>(prevPage);
  const [pageSize, setPageSize] = React.useState<number>(prevPageSize);
  const [searchFormVisible, setSearchFormVisible] = React.useState(
    searchFormVisiblePrev
  );

  const [searchValues, setSearchValues] = React.useState<Record<string, any>>();

  const [gridState, , apiRef] = useSmartDataGrid(config, {
    idField,
    getRowId,
    config,
    selection: [],
    rows: initRows
  });

  const { rows, selection, loadRev, paging, allRows } = gridState;

  const containerRef = React.useRef<HTMLDivElement>();
  const [topHeight, setTopHeight] = React.useState<number>(0);
  const topRef = React.useRef<HTMLDivElement>();
  const [key, setKey] = React.useState('');

  const topSizeObserver = React.useCallback(
    (_, height) => setTopHeight(height),
    []
  );

  useResizeDetector({
    onResize: topSizeObserver,
    targetRef: topRef
  });
  const gutter = 16;
  const hasFooter = paging?.last_page > 1 || paging?.total > prevPageSize;
  const footerHeight = hasFooter ? FOOTER_HEIGHT : 0;
  const maxHeight = useListContentHeight(containerRef) - gutter;
  const listHeight =
    height ?? maxHeight > 0
      ? maxHeight - footerHeight - topHeight - gutter - headerHeight
      : window.innerHeight * 0.5;
  const handleRowAction = React.useCallback(
    (type: string, payload?: object, meta?: object): void => {
      // complex action
      dispatch({
        type,
        payload,
        meta: { ...meta, apiRef }
      });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const handleGridAction = React.useCallback(
    (type: string, payload?: object, meta?: object): void => {
      // complex action
      dispatch({
        type,
        payload,
        meta: { ...meta, apiRef }
      });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const handleColumnAction = React.useCallback(
    (type: string, payload?: object, meta?: object): void => {
      // complex action
      dispatch({
        type,
        payload,
        meta: { ...meta, apiRef }
      });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const handleBatchAction = useCallback(
    (type: string, payload?: object, meta?: object) => {
      // push to saga then refresh content.

      dispatch({
        type,
        payload: { ...payload, id: selection },
        meta: { ...meta, apiRef }
      });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [selection]
  );

  const handleReloadRow = useCallback(
    (type: string, payload?: object, meta?: object) => {
      // push to saga then refresh content.

      dispatch({
        type,
        payload: { ...payload },
        meta: { ...meta, apiRef }
      });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [selection]
  );

  const handleToggleSelect = useCallback(
    (id: GridRowId) => apiRef.current.toggleSelect(id),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const [gridContext] = React.useState<DataGridContextShape>({
    classes,
    config,
    dispatch,
    handleRowAction,
    handleColumnAction,
    handleBatchAction,
    handleGridAction,
    handleReloadRow,
    setSearchValues,
    apiRef
  });

  const toggleSearchForm = () => setSearchFormVisible(open => !open);

  const pageParams = React.useMemo(() => {
    let shouldPage = page;

    // reset page when search form change (search from another block)
    if (!isEqual(prevPageContextParams, pageContextParams)) {
      shouldPage = 1;
      setPage(shouldPage);
    }

    return {
      ...pageContextParams,
      ...searchValues,
      limit: pageSize,
      loadRev,
      dataGridProps: undefined,
      gridName: undefined,
      appName: undefined,
      parentResource: undefined,
      resourceName: undefined,
      page: shouldPage
    };
  }, [
    pageContextParams,
    searchValues,
    pageSize,
    loadRev,
    page,
    prevPageContextParams
  ]);
  const handleNavigateSearch = React.useCallback(
    values => {
      // const action = searchForm?.action;

      // if (!action) return;

      // TODO: hot fix pathname, should remove when API update all app
      navigate(`${pathname}?${qs.stringify(values)}`, {
        replace: true
      });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [searchForm, pathname]
  );

  const handleSearchValuesChanged = (values: any, form: any) => {
    if (inlineSearch) {
      handleInlineSearch(values.q);
    } else {
      handleNavigateSearch(values);
    }

    if (form) form.setSubmitting(false);
  };

  React.useEffect(() => {
    const token = eventCenter.on(`dataGridChangeRows_${gridName}`, setKey);

    return () => eventCenter.off(`dataGridChangeRows_${gridName}`, token);
  }, [eventCenter, gridName]);

  const [forceRows, loading, error, meta] = useFetchItems({
    dataSource: fetchAction,
    pageParams,
    cache: false,
    data: initRows,
    // check compact params when has apiParam
    allowRiskParams,
    key
  });

  React.useEffect(() => {
    apiRef.current.initRows(forceRows, meta);
  }, [apiRef, forceRows, meta]);

  const handleInlineSearch = React.useCallback(
    (query: string) => {
      const filter = (row: GridRowData): string => {
        return inlineSearch.find(name =>
          new RegExp(escapeRegExp(query), 'ig').test(row[name])
        );
      };
      apiRef.current.setRows(allRows.filter(filter));
    },
    [apiRef, inlineSearch, allRows]
  );

  const handlePageChange = useCallback((evt: any, page: number) => {
    setPage(page);
  }, []);

  const variableListRef = React.useRef<VariableSizeList>();

  const itemSizeMap = React.useRef<Record<number, number>>({});

  const setItemSize = React.useCallback((index: number, height: number) => {
    // skip if equals
    if (itemSizeMap.current[index] === height) return;

    itemSizeMap.current[index] = height;

    if (variableListRef.current) {
      variableListRef.current.resetAfterIndex(index);
    }
  }, []);

  const count = selection.length;

  const ListItem = ({ index, style, row: rowProps }) => {
    // priority data from rowProps(sortable)
    const row = rowProps || rows[index];

    const id = getRowId(row);

    const selected = selection.includes(id);

    const height = !dynamicRowHeight ? { height: rowHeight } : {};

    return (
      <GridRow
        index={index}
        setItemSize={setItemSize}
        // selected={selected}
        padding={config.checkboxSelection ? '0 12px 0 0' : '0 12px'}
        onClick={
          disableSelectionOnClick ? undefined : () => handleToggleSelect(id)
        }
        style={style}
        {...height}
      >
        {columns.map((colDef, index) => {
          const CellRender = colDef.renderCell ?? BasicCell;

          return (
            <BodyCell
              flex={colDef.flex}
              width={colDef.width}
              align={colDef.align as any}
              minWidth={colDef.minWidth}
              truncateLines={colDef.truncateLines}
              key={index.toString()}
              {...height}
            >
              <CellRender
                selected={selected}
                colDef={colDef}
                id={id}
                row={row}
              />
            </BodyCell>
          );
        })}
      </GridRow>
    );
  };

  const Header = () => {
    return (
      <GridHead
        height={headerHeight}
        data-testid="GridHeader"
        padding={config.checkboxSelection ? '0 12px 0 0' : '0 12px'}
      >
        {columns.map((colDef, index) => {
          const CellRender = colDef.renderHeader ?? HeaderBasic;

          return (
            <CellRender
              colDef={colDef}
              key={index.toString()}
              searchValues={searchValues}
            />
          );
        })}
      </GridHead>
    );
  };

  if (!fetchAction && !initRows.length) {
    if (IS_ADMINCP) {
      return <Alert color="error">Missing required props.</Alert>;
    }

    return null;
  }

  if (error) return <ErrorComponent error={error} noWrapper />;

  const hasRows = rows.length > 0;
  const noResults = !loading && rows.length === 0;

  return (
    <DataGridContext.Provider value={gridContext}>
      <GridDataStateContext.Provider value={gridState}>
        <Wrapper ref={containerRef} data-testid="GridContainer">
          <Box ref={topRef} data-testid="GridTop">
            <GridBanner
              toggleSearchForm={toggleSearchForm}
              title={title}
              description={description}
              hideDescription={hideDescription || !description}
              hideTitle={hideTitle || !title}
              multipleSelection={checkboxSelection}
              hideToggleSearch={hideToggleSearch}
            />
            {(remoteSearchForm || searchForm) &&
            searchFormPlacement === 'header' ? (
              <GridSearchForm
                dataSource={remoteSearchForm}
                visibility={searchFormVisible}
                searchValues={searchValues}
                formSchema={searchForm}
                onSearchChange={handleSearchValuesChanged}
                hideDescription={hideDescription || !description}
                hideTitle={hideTitle || !title}
              />
            ) : null}
            <GridToolbar
              formSchema={
                searchFormPlacement === 'top' ? searchForm : undefined
              }
              onSearchChange={handleSearchValuesChanged}
              selectionCount={count}
              disabled={!count}
              multipleSelection={checkboxSelection}
              handleAction={handleBatchAction}
              menu={batchActionMenu}
              gridActionMenu={gridActionMenu}
              hideDescription={hideDescription || !description}
              hideTitle={hideTitle || !title}
              totalItems={
                show_total ? (
                  <TotalItems message={total_item_phrase} total={meta?.total} />
                ) : null
              }
            />
          </Box>
          <GridMain>
            <Header />
            <GridBody
              minHeight={additionalSection ? undefined : listHeight}
              data-testid="GridBody"
            >
              {sortable ? (
                <DataGridDnD
                  meta={{ apiRef }}
                  rows={rows}
                  component={ListItem}
                />
              ) : (
                <Box>
                  {rows.map((x, i) => (
                    <Box key={`k${x?.id}`}>{ListItem({ index: i })}</Box>
                  ))}
                </Box>
              )}
              {loading && !hasRows ? <Loading /> : null}
              {!loading && noResults ? (
                <NoResults message={meta?.empty_message} />
              ) : null}
            </GridBody>
          </GridMain>
          {hasFooter ? (
            <Footer
              height={FOOTER_HEIGHT}
              paging={paging}
              onRowsPerPageChange={setPageSize}
              multipleSelection={checkboxSelection}
              onPageChange={handlePageChange}
              rowsPerPageOptions={rowsPerPageOptions}
            />
          ) : null}
        </Wrapper>
        {additionalSection ? (
          <Box sx={{ mt: 2 }}>
            <DataGridAdditionalSection config={additionalSection} />
          </Box>
        ) : null}
      </GridDataStateContext.Provider>
    </DataGridContext.Provider>
  );
}
