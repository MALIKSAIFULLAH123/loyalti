import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export default makeStyles((theme: Theme) =>
  createStyles({
    root: {},
    outer: {
      position: 'relative',
      display: 'flex',
      borderRadius: theme.shape.borderRadius,
      border: theme.mixins.border('secondary'),
      backgroundColor: theme.mixins.backgroundColor('paper'),
      minHeight: 160
    },
    header: {
      position: 'relative',
      display: 'flex',
      padding: '20px 20px 0 0',
      '& .ico': {
        fontSize: 13,
        color: theme.palette.text.secondary
      }
    },
    title: {
      flex: 1,
      color: theme.palette.text.primary,
      marginTop: theme.spacing(1),
      paddingRight: theme.spacing(2)
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
      position: 'relative',
      marginLeft: theme.spacing(2),
      flex: 1,
      minWidth: 0
    },
    media: {
      width: 160,
      maxWidth: 160,
      borderTopLeftRadius: theme.shape.borderRadius,
      borderBottomLeftRadius: theme.shape.borderRadius,
      overflow: 'hidden'
    },
    itemFlag: {
      position: 'absolute',
      right: -2,
      bottom: theme.spacing(1.5),
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'flex-end'
    },
    smallView: {
      '& $outer': {
        minHeight: 'auto',
        border: 'none',
        borderRadius: 0
      },
      '& $inner': {
        marginLeft: theme.shape.borderRadius
      },
      '& $media': {
        maxWidth: '56px',
        borderRadius: 0
      },
      '& $header': {
        padding: '0 8px 0 0'
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
