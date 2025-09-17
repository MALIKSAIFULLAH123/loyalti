import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const styles = (theme: Theme) =>
  createStyles({
    isDragging: {},
    itemRoot: {
      width: '100%',
      backgroundColor: theme.mixins.backgroundColor('paper'),
      borderRadius: theme.shape.borderRadius,
      boxShadow: theme.shadows[2],
      '&$isDragging': {
        width: '100%',
        border: 'dashed 2px',
        borderColor: theme.palette.primary.main
      }
    },
    itemMediaBackdrop: {
      backgroundColor: theme.mixins.backgroundColor('paper'),
      textAlign: 'center',
      overflow: 'hidden',
      width: '100%',
      display: 'flex',
      justifyContent: 'center',
      alignItems: 'center',
      paddingBottom: '56%',
      borderRadius: theme.shape.borderRadius
    },
    ratioauto: {},
    ratio169: {
      paddingBottom: '56.25%'
    },
    ratio916: {
      paddingBottom: '177.77%'
    },
    ratio32: {
      paddingBottom: '66.66%'
    },
    ratio23: {
      paddingBottom: '150%'
    },
    ratio43: {
      paddingBottom: '75%'
    },
    ratio34: {
      paddingBottom: '133.33%'
    },
    ratio13: {
      paddingBottom: '31%'
    },
    ratio11: {
      paddingBottom: '100%'
    },
    itemMedia: {
      position: 'absolute',
      left: 0,
      top: 0,
      width: '100%',
      height: '100%',
      borderRadius: theme.shape.borderRadius
    }
  });

export default makeStyles(styles);
