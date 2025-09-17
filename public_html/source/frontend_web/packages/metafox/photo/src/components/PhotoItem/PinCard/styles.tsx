import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      pinGird: {
        width: '100%'
      },
      mediaLink: {
        display: 'block',
        float: 'left'
      },
      pinItem: {
        position: 'relative',
        flexBasis: 0,
        display: 'flex',
        flexDirection: 'column',
        flexGrow: 1,
        '&:hover': {
          '& $photoInfo': {
            opacity: 1
          }
        }
      },
      image: {
        maxWidth: '100%',
        maxHeight: '100%',
        minWidth: '100%',
        float: 'left',
        display: 'flex'
      },
      photoActions: {
        position: 'absolute',
        bottom: theme.spacing(1),
        right: theme.spacing(2),
        backgroundColor: 'rgba(0,0,0,.4)',
        textAlign: 'center',
        borderRadius: '100%'
      },
      photoActionsDropdown: {
        color: '#fff',
        '& .ico': {
          fontSize: theme.mixins.pxToRem(13),
          color: '#fff'
        }
      },
      iconButton: {
        color: '#fff'
      },
      features: {
        position: 'absolute',
        top: theme.spacing(2),
        right: '0',
        display: 'flex',
        flexFlow: 'column',
        alignItems: 'flex-end',
        zIndex: 3
      },
      btnRemove: {
        position: 'absolute',
        bottom: 0,
        zIndex: 2
      },
      photoInfo: {
        position: 'absolute',
        bottom: 0,
        left: 0,
        right: 0,
        top: 0,
        transition: 'all .2s ease',
        padding: theme.spacing(2),
        opacity: 0,
        color: '#fff',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: 'rgba(0,0,0,0.6)'
      },
      playVideoIcon: {
        position: 'absolute',
        bottom: 0,
        left: 0,
        right: 0,
        top: 0,
        padding: theme.spacing(2),
        color: '#fff',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: 'rgba(0,0,0,0.6)',
        fontSize: theme.spacing(5.5)
      },
      photoTitle: {
        whiteSpace: 'nowrap',
        textOverflow: 'ellipsis',
        overflow: 'hidden',
        fontWeight: theme.typography.fontWeightBold,
        fontSize: theme.mixins.pxToRem(15)
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
      total_like: {
        margin: theme.spacing(0, 0.75),
        fontSize: theme.mixins.pxToRem(14),
        lineHeight: '14px',
        marginTop: '1px'
      },
      hoverCard: {
        '& $photoInfo': {
          opacity: 1
        }
      }
    }),
  { name: 'PhotoPinItemView' }
);
