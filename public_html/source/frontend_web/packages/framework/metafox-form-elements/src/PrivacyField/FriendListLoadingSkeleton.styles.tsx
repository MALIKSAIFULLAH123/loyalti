import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        height: theme.spacing(5),
        display: 'flex',
        alignItems: 'center'
      },
      checkbox: {
        marginRight: theme.spacing(1)
      }
    }),
  { name: 'DialogCustomListPrivacy' }
);
