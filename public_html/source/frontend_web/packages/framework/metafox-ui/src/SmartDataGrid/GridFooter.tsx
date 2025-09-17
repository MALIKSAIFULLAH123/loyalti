import {
  GridDataState,
  SmartDataGridProps,
  useGlobal
} from '@metafox/framework';
import { Box, Pagination, TablePagination } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

interface Props {
  width: number;
  height: number;
  paging: GridDataState['paging'];
  onRowsPerPageChange?: (value: number) => void;
  onPageChange?: (event: unknown, value: number) => void;
  rowsPerPageOptions?: SmartDataGridProps['rowsPerPageOptions'];
  multipleSelection?: boolean;
  onResize?: (width?: number, height?: number) => void;
}

const DataGridFooterRoot = styled(Box, {
  name: 'SmartDataGrid',
  slot: 'Footer',
  shouldForwardProp: (prop: string) => !/width|height/i.test(prop)
})<{ height: number; width: number }>(({ theme, width, height }) => ({
  height,
  width: width ?? '100%',
  position: 'relative',
  borderTop: theme.mixins.border('secondary'),
  display: 'flex',
  flexDirection: 'row',
  alignItems: 'center',
  padding: '0 8px 0 0',
  '& button': {
    color: 'inherit'
  },
  [theme.breakpoints.up('sm')]: {
    flexDirection: 'row-reverse'
  }
}));

export default function SmartDataGridFooter(props: Props) {
  const {
    paging,
    onPageChange,
    onRowsPerPageChange,
    height,
    rowsPerPageOptions,
    width
  } = props;

  const { i18n } = useGlobal();

  if (rowsPerPageOptions)
    // ⚠️ while the TablePagination page prop starts at 0 issues.
    return (
      <DataGridFooterRoot
        height={height}
        width={width}
        data-testid="GridFooter"
      >
        <TablePagination
          onPageChange={(evt, page) => onPageChange(evt, page + 1)}
          page={paging?.current_page - 1}
          count={paging?.total}
          rowsPerPage={paging?.per_page}
          onRowsPerPageChange={evt => {
            onPageChange(evt, 1);
            onRowsPerPageChange(evt.target.value as unknown as number);
          }}
          showFirstButton={paging?.current_page > 0}
          showLastButton={paging?.current_page < paging?.last_page}
          component="div"
          labelRowsPerPage={i18n.formatMessage({ id: 'rows_per_page' })}
          rowsPerPageOptions={rowsPerPageOptions}
        />
      </DataGridFooterRoot>
    );

  return (
    <DataGridFooterRoot height={height} width={width} data-testid="GridFooter">
      <Pagination
        onChange={onPageChange}
        page={paging?.current_page}
        count={paging?.last_page}
        showFirstButton={paging?.current_page > 1}
        showLastButton={paging?.current_page < paging?.last_page}
      />
    </DataGridFooterRoot>
  );
}
