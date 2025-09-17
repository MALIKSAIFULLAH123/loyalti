import React from 'react';
import { Box, styled } from '@mui/material';
import useDataGridContext from './useDataGridContext';
import LineIcon from '../LineIcon';
import { Hint } from '@metafox/ui';

interface Props {
  colDef: any;
  children: any;
  searchValues?: Record<string, any>;
}

const Root = styled('div', {
  name: 'DataGrid',
  slot: 'Cell',
  shouldForwardProp(propName: string): boolean {
    return !/width|height|flex|align|minWidth|sortable/i.test(propName);
  }
})<{
  sortable?: boolean;
  width?: number;
  height?: number;
  minWidth?: number;
  flex?: number;
  align: 'left' | 'right' | 'center' | undefined;
}>(({ sortable, align, width, height, minWidth, flex }) => ({
  display: 'flex',
  alignItems: 'center',
  padding: '10px 4px',
  overflow: 'hidden',
  justifyContent: align ?? 'start',
  minWidth,
  flex,
  width,
  height,
  textAlign: align ?? 'left',
  fontWeight: 'bold',
  cursor: sortable ? 'pointer' : 'unset'
}));

const getSortIcon = (sortType: string) => {
  if (sortType === 'asc') return 'ico-arrow-up';

  if (sortType === 'desc') return 'ico-arrow-down';

  return undefined;
};

export default function HeaderCell({ colDef, children, searchValues }: Props) {
  const { setSearchValues } = useDataGridContext();
  const {
    headerAlign,
    align,
    minWidth,
    width,
    flex,
    headerHeight,
    sortable,
    description,
    sortableField
  } = colDef;

  const { sort, sort_type } = searchValues || {};

  const canSort = sortableField && sortable;

  const sortRow = () => {
    setSearchValues(prev => ({
      ...prev,
      sort: sortableField,
      sort_type: sort_type === 'desc' ? 'asc' : 'desc'
    }));
  };

  return (
    <Root
      align={headerAlign ?? align}
      minWidth={minWidth}
      width={width}
      flex={flex}
      sortable={sortable}
      height={headerHeight}
      onClick={() => canSort && sortRow()}
    >
      {children}
      {sortable && sort_type && sort === sortableField && (
        <LineIcon icon={getSortIcon(sort_type as string)} />
      )}
      <Box sx={{ pl: 0.5 }}>
        <Hint>{description}</Hint>
      </Box>
    </Root>
  );
}
