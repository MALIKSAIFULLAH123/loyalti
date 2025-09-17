import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'flex',
        position: 'relative'
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
      searchFocused: {
        border: theme.mixins.border('primary'),
        background: 'none'
      },
      searchIcon: {
        top: 0,
        width: theme.mixins.pxToRem(32),
        height: '100%',
        position: 'absolute',
        pointerEvents: 'none',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        transition: 'all .2s ease',
        cursor: 'pointer'
      },
      searchIconFocused: {
        color: theme.palette.primary.main
      },
      inputRoot: {
        color: 'inherit',
        width: '100%'
      },
      inputInput: {
        padding: theme.spacing(1, 2, 1, 0),
        boxSizing: 'border-box',
        // vertical padding + font size from searchIcon
        paddingLeft: '30px !important',
        transition: 'all .2s ease',
        width: '100%',
        height: theme.mixins.pxToRem(32)
      }
    }),
  { name: 'SearchInModule' }
);

export default useStyles;
