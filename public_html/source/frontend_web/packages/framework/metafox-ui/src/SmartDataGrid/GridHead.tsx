import { Box, styled } from '@mui/material';

const GridHead = styled(Box, {
  name: 'DataGrid',
  slot: 'Header',
  shouldForwardProp(prop: string): boolean {
    return !/padding|height|width/i.test(prop);
  }
})<{ padding?: string; height?: number; width?: number }>(
  ({ height, padding, width, theme }) => ({
    display: 'flex',
    flexDirection: 'row',
    height,
    boxSizing: 'border-box',
    width: width ?? '100%',
    // visibility: width ? 'visible' : 'hidden',
    // maxWidth: '100%',
    // minWidth: '100%', // causing issue width scrollbar="always" on macOS
    padding,
    '.dataGrid-scroll &': {
      borderBottom: '0 !important',
      '& > div': {
        borderBottom: theme.mixins.border('secondary')
      }
    },
    borderBottom: theme.mixins.border('secondary')
  })
);

export default GridHead;
