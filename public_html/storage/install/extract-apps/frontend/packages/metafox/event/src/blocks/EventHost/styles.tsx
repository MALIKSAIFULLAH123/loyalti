import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'flex',
        alignItems: 'center'
      },
      avatar: {},
      name: {
        fontSize: theme.mixins.pxToRem(15),
        fontWeight: 'bold',
        marginLeft: theme.spacing(1.5),
        '& a': {
          color: theme.palette.text.primary
        }
      }
    }),
  { name: 'MuiEventHost' }
);
