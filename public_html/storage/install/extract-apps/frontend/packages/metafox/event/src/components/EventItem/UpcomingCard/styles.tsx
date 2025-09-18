import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

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
      '& span': {
        color: theme.palette.text.primary,
        fontWeight: theme.typography.fontWeightBold
      }
    },
    itemMinor: {
      color: theme.palette.text.secondary,
      fontSize: theme.mixins.pxToRem(13),
      lineHeight: 1.2,
      marginTop: theme.spacing(1),
      '& a': {
        color: theme.palette.text.secondary
      }
    },
    startDate: {
      color: theme.palette.text.secondary
    },
    location: {
      overflow: 'hidden',
      maxWidth: '100%',
      whiteSpace: 'nowrap',
      textOverflow: 'ellipsis'
    },
    inner: {
      margin: theme.spacing(2)
    },
    media: {
      display: 'block',
      borderTopLeftRadius: theme.shape.borderRadius,
      borderTopRightRadius: theme.shape.borderRadius,
      overflow: 'hidden'
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
    eventActions: {
      display: 'flex',
      marginTop: theme.spacing(2)
    },
    iconButton: {
      flex: 1,
      minWidth: 0,
      marginRight: theme.spacing(1),
      border: '1px solid',
      height: '40px',
      borderRadius: theme.spacing(0.5),
      display: 'flex',
      alignItems: 'center',
      color: theme.palette.primary.main,
      justifyContent: 'center',
      fontSize: theme.mixins.pxToRem(15),
      '& .ico': {
        fontSize: theme.mixins.pxToRem(18),
        marginRight: theme.spacing(1)
      }
    },
    actionMenu: {
      border: '1px solid',
      width: '40px',
      height: '40px',
      borderRadius: theme.spacing(0.5),
      display: 'flex',
      alignItems: 'center',
      color: theme.palette.primary.main,
      justifyContent: 'center'
    },
    bgImage: {
      display: 'block',
      backgroundSize: 'cover',
      backgroundPosition: 'center center',
      backgroundRepeat: 'no-repeat',
      backgroundOrigin: 'border-box',
      '&:before': {
        content: '""',
        display: 'block',
        paddingBottom: '35.862%'
      }
    },
    upcomingItem: {
      '& $outer': {
        border: 'none'
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
