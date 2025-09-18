import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export default makeStyles((theme: Theme) =>
  createStyles({
    root: {
      height: '100%'
    },
    outer: {
      position: 'relative',
      borderRadius: theme.shape.borderRadius,
      border: theme.mixins.border('secondary'),
      backgroundColor: theme.mixins.backgroundColor('paper'),
      height: '100%'
    },
    title: {
      color: theme.palette.text.primary
    },
    itemMinor: {
      color: theme.palette.text.secondary,
      fontSize: 13,
      lineHeight: 1.2,
      marginTop: theme.spacing(1),
      '& a': {
        color: theme.palette.text.secondary
      }
    },
    inner: {
      margin: theme.spacing(2)
    },
    media: {
      display: 'block',
      borderTopLeftRadius: theme.shape.borderRadius,
      borderTopRightRadius: theme.shape.borderRadius,
      overflow: 'hidden',
      '&:hover': {
        position: 'relative',
        '&:after': {
          content: '""',
          position: 'absolute',
          top: 0,
          bottom: 0,
          left: 0,
          right: 0,
          backgroundColor: '#000',
          border: theme.mixins.border('secondary'),
          opacity: 0.4,
          cursor: 'pointer'
        },
        '& $iconPlay': {
          display: 'block',
          position: 'absolute',
          top: '50%',
          left: '50%',
          transform: 'translate(-50%, -50%)',
          zIndex: 1,
          color: '#fff',
          fontSize: 48,
          cursor: 'pointer'
        }
      }
    },
    iconPlay: {
      display: 'none'
    },
    itemFlag: {
      position: 'absolute',
      right: -2,
      top: theme.spacing(2),
      zIndex: 1,
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'flex-end'
    },
    actionMenu: {
      position: 'absolute !important',
      right: theme.spacing(2),
      bottom: theme.spacing(2),
      '& .ico': {
        fontSize: 13,
        color: theme.palette.text.secondary
      }
    },
    smallView: {
      '& $outer': {
        minHeight: 'auto',
        border: 'none',
        borderRadius: 0
      },
      '& $inner': {
        marginLeft: theme.spacing(1)
      },
      '& $media': {
        maxWidth: '56px',
        borderRadius: 0
      },
      '& $title': {
        fontWeight: '400',
        color: theme.palette.text.primary,
        marginTop: 0
      },
      '& $itemMinor': {
        marginTop: theme.spacing(0.5)
      }
    }
  })
);
