import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        height: 'auto',
        '&.MuiAlert-filledSuccess': {
          backgroundColor: `${theme.palette.primary.main} !important`,
          color: '#fff'
        },
        '&.MuiAlert-filledInfo': {
          backgroundColor:
            theme.palette.mode === 'light'
              ? `${theme.palette.grey['800']} !important`
              : `${theme.palette.grey['200']} !important`,
          color: theme.palette.grey['500']
        }
      },
      action: {
        padding: 0
      },
      standardSuccess: {
        backgroundColor: theme.palette.primary.main,
        color: '#fff'
      },
      standardError: {
        backgroundColor: theme.palette.error.main,
        color: '#fff'
      },
      message: {
        fontSize: theme.mixins.pxToRem(15),
        display: 'flex',
        alignItems: 'center'
      }
    }),
  { name: 'ToastItem' }
);
