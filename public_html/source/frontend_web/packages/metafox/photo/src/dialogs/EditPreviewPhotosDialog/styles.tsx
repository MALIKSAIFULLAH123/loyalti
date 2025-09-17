import { Theme } from '@mui/material';
import { createStyles } from '@mui/styles';

const styles = (theme: Theme) =>
  createStyles({
    dialogTitle: {},
    dialogContent: {
      backgroundColor: theme.palette.background.default,
      minWidth: 550,
      minHeight: 500
    },
    dialogActions: {},
    itemListContainer: {
      display: 'flex',
      flexWrap: 'wrap',
      padding: theme.spacing(2),
      justifyContent: 'space-between'
    },
    isDragging: {},
    itemRoot: {
      width: 'calc(50% - 8px)',
      backgroundColor: theme.mixins.backgroundColor('paper'),
      marginBottom: theme.spacing(2),
      borderRadius: theme.shape.borderRadius,
      boxShadow: theme.shadows[2],
      '&:hover $autoHideButton': {
        visibility: 'visible'
      },
      '&$isDragging': {
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
      height: '33vh',
      borderTopLeftRadius: theme.shape.borderRadius,
      borderTopRightRadius: theme.shape.borderRadius
    },
    itemBlur: {
      width: '10%',
      height: '10%',
      transform: 'scale(14)',
      filter: 'blur(3px)',
      WebkitFilter: 'blur(3px)'
    },
    itemBlurImg: {
      objectFit: 'cover',
      width: '100%',
      height: '100%',
      border: 0
    },
    itemMediaContainer: {
      position: 'relative',
      textAlign: 'center'
    },
    itemMedia: {
      position: 'absolute',
      objectFit: 'contain',
      left: 0,
      top: 0,
      width: '100%',
      height: '100%'
    },
    itemComposerContainer: {
      padding: theme.spacing(1)
    },
    itemComposer: {},
    autoHideButton: {
      visibility: 'hidden'
    },
    closeButton: {
      position: 'absolute',
      top: theme.spacing(2),
      right: theme.spacing(2)
    },
    tagButton: {
      position: 'absolute',
      bottom: theme.spacing(2),
      left: theme.spacing(2)
    },
    editButton: {
      position: 'absolute',
      left: theme.spacing(2),
      top: theme.spacing(2)
    }
  });

export default styles;
