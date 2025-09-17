import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'block',
        position: 'relative'
      },
      map: {
        display: 'block',
        height: 300,
        backgroundColor: theme.palette.action.focus
      },
      hideMap: {
        display: 'none !important'
      },
      popper: {
        zIndex: 1300,
        width: '100%'
      },
      paper: {
        padding: theme.spacing(1, 1)
      },
      suggestItem: {
        display: 'flex',
        flexDirection: 'row',
        padding: theme.spacing(1, 1),
        fontSize: theme.mixins.pxToRem(15),
        cursor: 'pointer',
        '&:hover': {
          backgroundColor: theme.palette.action.focus
        }
      },
      suggestIcon: {
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(13),
        paddingRight: theme.spacing(1),
        paddingTop: theme.spacing(0.5)
      },
      suggestName: {
        color: theme.palette.text.primary
      },
      suggestAddress: {
        color: theme.palette.text.secondary
      }
    }),
  {
    name: 'formFooter'
  }
);
