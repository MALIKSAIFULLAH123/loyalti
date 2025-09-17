import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      sticky: {
        backgroundColor: theme.mixins.backgroundColor('paper'),
        borderBottom: 'solid 1px',
        borderBottomColor: theme.palette.border?.secondary,
        borderRadius: 0,
        position: 'fixed',
        top: theme.appBarHeight?.fixed - 1,
        zIndex: 1000
      }
    }),
  {
    name: 'StickyBar'
  }
);
