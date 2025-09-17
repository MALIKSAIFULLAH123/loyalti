import { styled } from '@mui/material';
import * as React from 'react';

type TextAlgin = React.CSSProperties['textAlign'];

interface Props {
  align?: TextAlgin;
  width?: number;
  minWidth?: number;
  dense?: boolean;
  flex?: number;
  height?: number;
  truncateLines?: number;
}

const GridCell = styled('div', {
  name: 'DataGrid',
  slot: 'Cell',
  shouldForwardProp: (prop: string) => {
    return !/align|dense|flex|width|minWidth|truncateLines/i.test(prop);
  }
})<Props>(({ theme, height, align, dense, flex, minWidth, width }) => ({
  display: 'flex',
  alignItems: 'center',
  flex,
  width,
  minWidth,
  padding: dense ? '4px' : '10px 4px',
  textAlign: align ?? 'left',
  justifyContent: align ?? 'left',
  overflow: 'hidden',
  height
}));

export default GridCell;
