import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        backgroundColor: theme.palette.background.paper,
        borderRadius: theme.shape.borderRadius,
        transition: 'all 0.2s ease',
        position: 'relative',
        height: '100%',
        border: theme.mixins.border('secondary'),
        '&:hover': {
          boxShadow: theme.shadows[4]
        },
        '&:hover $actionsDropdown': {
          opacity: 1
        }
      },
      smallRoot: {},
      outer: {
        position: 'relative'
      },
      inner: {
        position: 'relative',
        padding: theme.spacing(2)
      },
      title: {},
      mediaContent: {
        position: 'relative'
      },
      totalPhoto: {
        color: theme.palette.text.secondary,
        fontSize: '0.8125rem',
        lineHeight: '16px',
        display: 'flex',
        marginTop: theme.spacing(1)
      },
      smallTotalPhoto: {
        height: '20px',
        borderRadius: '6px',
        backgroundColor: 'rgba(0,0,0,0.5)',
        color: '#fff',
        fontSize: '12px',
        lineHeight: '16px',
        position: 'absolute',
        bottom: '8px',
        right: '8px',
        padding: '0 8px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        '& .ico-photos-alt-o': {
          marginRight: theme.spacing(0.5)
        }
      },
      media: {
        display: 'block',
        position: 'relative',
        borderTopLeftRadius: theme.shape.borderRadius,
        borderTopRightRadius: theme.shape.borderRadius,
        overflow: 'hidden'
      },
      smallMedia: {
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
      owner: {
        fontSize: '0.8125rem',
        lineHeight: '16px',
        color: theme.palette.text.secondary,
        marginTop: theme.spacing(1),
        marginBottom: theme.spacing(1),
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap',
        display: 'block',
        '& b': {
          color: theme.palette.text.primary
        },
        '&:hover b': {
          textDecoration: 'underline'
        }
      },
      features: {
        position: 'absolute',
        zIndex: 1,
        top: theme.spacing(2),
        right: '-2px',
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'flex-end'
      },
      actionsDropdown: {
        position: 'absolute',
        bottom: theme.spacing(2),
        right: theme.spacing(2),
        opacity: 0
      },
      iconButton: {
        fontSize: '0.8125rem'
      },
      number: {
        marginRight: theme.spacing(0.5)
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
  { name: 'MuiPhotoAlbumItemView' }
);

export default useStyles;
