import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        width: '100%'
      },
      outer: {
        position: 'relative',
        '&:hover': {
          '& $photoInfo': {
            opacity: 1
          }
        }
      },
      inner: {
        position: 'absolute',
        bottom: 0,
        left: 0,
        right: 0,
        padding: theme.spacing(1),
        color: '#fff',
        opacity: 0
      },
      title: {
        fontWeight: 'bold'
      },
      media: {
        display: 'block',
        position: 'relative'
      },
      mediaBg: {
        display: 'block',
        position: 'relative',
        backgroundSize: 'cover',
        backgroundPosition: 'center center',
        backgroundRepeat: 'no-repeat',
        backgroundOrigin: 'border-box',
        border: '1px solid rgba(0, 0, 0, 0.1)',
        transition: 'all 300ms ease',
        '&::before': {
          content: '""',
          display: 'block',
          paddingBottom: '53%'
        }
      },
      mediaLink: {},
      statistic: {
        fontSize: '14px',
        marginBottom: theme.spacing(0.5)
      },
      owner: {
        color: '#fff',
        fontSize: '13px'
      },
      count: {
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: 'rgba(0,0,0,0.5)',
        borderRadius: theme.spacing(1.5),
        WebkitTransition: 'all 300ms ease',
        OTransition: 'all 300ms ease',
        transition: 'all 300ms ease',
        color: '#fff',
        padding: theme.spacing(0.5, 1),
        '& > .ico-thumbup': {
          marginRight: theme.spacing(0.5)
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
      photoActionsDropdown: {
        color: `${theme.palette.background.paper} !important`,
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
        transition: 'all .2s ease',
        padding: theme.spacing(2),
        opacity: 0,
        color: '#fff',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: 'rgba(0,0,0,0.6)'
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
      },
      skeleton: {
        height: 'auto',
        '&:before': {
          content: '""',
          display: 'block'
        }
      },
      ratioauto: {
        '&:before': {
          display: 'none'
        }
      },
      ratio169: {
        '&:before': {
          paddingBottom: '56.25%'
        }
      },
      ratio32: {
        '&:before': {
          paddingBottom: '66.66%'
        }
      },
      ratio23: {
        '&:before': {
          paddingBottom: '150%'
        }
      },
      ratio43: {
        '&:before': {
          paddingBottom: '75%'
        }
      },
      ratio34: {
        '&:before': {
          paddingBottom: '133.33%'
        }
      },
      ratio11: {
        '&:before': {
          paddingBottom: '100%'
        }
      }
    }),
  { name: 'MuiPhotoItemView' }
);

export default useStyles;
