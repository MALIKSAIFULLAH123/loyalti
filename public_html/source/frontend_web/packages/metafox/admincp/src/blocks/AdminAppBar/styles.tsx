import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      menuButton: {
        display: 'flex',
        alignItems: 'center',
        padding: '0 4px'
      },
      divider: {
        position: 'relative',
        '&:before': {
          content: '""',
          position: 'absolute',
          left: '0',
          top: '10px',
          bottom: '10px',
          width: '1px',
          backgroundColor: '#eaeaea'
        }
      },
      popper: {
        boxShadow: theme.shadows[20],
        zIndex: theme.zIndex.snackbar,
        width: '300px',
        backgroundColor: theme.palette.background.paper,
        borderRadius: theme.shape.borderRadius,
        maxHeight: '70vh',
        overflow: 'hidden'
      },
      menuItemViewSite: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        color: theme.palette.primary.main,
        border: 'none',
        position: 'relative',
        width: 'auto',
        padding: '15px 8px',
        fontSize: theme.mixins.pxToRem(15),
        '& i': {
          fontSize: theme.mixins.pxToRem(22),
          paddingLeft: theme.spacing(1.5)
        }
      },
      userAvatarButton: { display: 'flex', padding: '12px 8px' },
      userAvatarInfo: {
        paddingLeft: theme.spacing(1.5),
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'center'
      },
      userAvatar: { width: 32, height: 32 },
      userAvatarName: {
        color: theme.palette.text.primary,
        fontSize: theme.mixins.pxToRem(12)
      },
      userAvatarRole: {
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(12)
      },
      smallMenuButton: {
        cursor: 'pointer',
        margin: 0,
        padding: 0,
        color: theme.palette.text.secondary,
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        height: '32px',
        width: '32px',
        fontSize: theme.mixins.pxToRem(22),
        '& .MuiBadge-badge': {
          marginTop: 0
        }
      }
    }),
  {
    name: 'AdminAppBar'
  }
);

export default useStyles;
