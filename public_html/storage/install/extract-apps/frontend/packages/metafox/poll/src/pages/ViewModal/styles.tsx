import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        padding: '0 !important',
        overflowY: 'visible',
        display: 'flex'
      },
      titleDialog: {
        display: 'flex',
        alignItems: 'center'
      },
      closeDialog: {
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(13),
        marginLeft: 'auto',
        cursor: 'pointer'
      },
      contentWrapper: {
        width: '100%',
        maxWidth: '100%'
      }
    }),
  { name: 'MuiPollViewModal' }
);

export default useStyles;
