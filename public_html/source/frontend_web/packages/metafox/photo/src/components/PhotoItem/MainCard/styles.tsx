import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        width: '100%',
        position: 'relative',
        '&:hover $photoInfo': {
          opacity: 1
        }
      },
      photoActionsDropdown: {
        '& .ico': {
          fontSize: theme.mixins.pxToRem(13),
          color: '#fff'
        }
      },
      features: {
        position: 'absolute',
        top: theme.spacing(2),
        right: '-2px',
        display: 'flex',
        flexFlow: 'column',
        alignItems: 'flex-end'
      },
      photoInfo: {
        position: 'absolute',
        bottom: 0,
        left: 0,
        right: 0,
        top: 0,
        pointerEvents: 'none',
        transition: 'all .2s ease',
        padding: theme.spacing(2),
        flexDirection: 'column',
        opacity: 0,
        color: '#fff',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: 'rgba(0,0,0,0.6)'
      },
      photoTitle: {
        width: '100%',
        overflow: 'hidden',
        textAlign: 'center'
      },
      photoLike: {
        marginTop: theme.spacing(1),
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize: theme.mixins.pxToRem(13),
        '& .ico': {
          margin: theme.spacing(0, 0.75)
        }
      },
      photoActions: {
        position: 'absolute',
        bottom: theme.spacing(1),
        right: theme.spacing(1),
        backgroundColor: 'rgba(0,0,0,.4)',
        textAlign: 'center',
        borderRadius: '100%'
      },
      total_like: {
        margin: theme.spacing(0, 0.75),
        fontSize: theme.mixins.pxToRem(14),
        lineHeight: '14px',
        marginTop: '1px'
      }
    }),
  { name: 'MuiPhotoItemView' }
);

export default useStyles;
