import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'block',
        borderRadius: theme.shape.borderRadius,
        position: 'relative'
      },
      listContainer: {
        position: 'relative',
        display: 'flex',
        flexWrap: 'wrap'
      },
      itemInner: {
        maxWidth: '100%'
      },
      itemRoot: {
        position: 'relative',
        display: 'flex',
        flexBasis: '50%',
        padding: 1
      },
      item0: {},
      item1: {},
      item2: {},
      item3: {},
      item4: {},
      item: {
        display: 'block',
        padding: theme.spacing(0.25)
      },
      preset1: {
        '& $item0': {
          flexBasis: '100%'
        }
      },
      preset2: {},
      preset3: {
        '& $item0': {
          flexBasis: '100%'
        }
      },
      preset4: {},
      remainBackdrop: {
        position: 'absolute',
        left: 0,
        right: 0,
        top: 0,
        bottom: 0,
        backgroundColor: 'rgba(0,0,0,0.3)',
        '&:hover': {
          backgroundColor: 'rgba(0,0,0,0.1)'
        }
      },
      remainText: {
        color: 'white',
        position: 'absolute',
        left: '50%',
        top: '50%',
        fontSize: '2rem',
        transform: 'translate(-50%,-50%)'
      },
      actionBar: {
        position: 'absolute',
        top: 0,
        right: 0,
        display: 'flex',
        flexDirection: 'row-reverse',
        padding: theme.spacing(2)
      },
      closeButton: {
        backgroundColor: 'rgba(0,0,0,0.7)',
        '&:hover': {
          backgroundColor: 'rgba(0,0,0,0.8)'
        },
        '& span': {
          color: 'white'
        }
      }
    }),
  { name: 'PhotoPreviews' }
);

export default useStyles;
