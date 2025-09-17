import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      popper: {
        boxShadow: theme.shadows[10],
        zIndex: theme.zIndex.snackbar,
        minWidth: '180px',
        width: 'auto',
        backgroundColor: theme.palette.background.paper,
        borderRadius: theme.shape.borderRadius,
        maxHeight: '70vh',
        overflow: 'hidden'
      },
      menu: { padding: theme.spacing(1, 0) }
    }),
  { name: 'DropdownMenu' }
);

export default useStyles;
