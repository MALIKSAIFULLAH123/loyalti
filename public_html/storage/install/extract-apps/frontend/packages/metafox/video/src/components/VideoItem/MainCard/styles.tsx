import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export default makeStyles((theme: Theme) =>
  createStyles({
    itemFlag: {
      position: 'absolute',
      right: 0,
      top: theme.spacing(2),
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'flex-end',
      zIndex: 5
    },
    iconPlay: {
      display: 'none'
    },
    itemMedia: {
      position: 'relative',
      '&:hover': {
        '&:after': {
          content: '""',
          position: 'absolute',
          top: 0,
          bottom: 0,
          left: 0,
          right: 0,
          backgroundColor: '#000',
          border: theme.mixins.border('secondary'),
          opacity: 0.4
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
    itemTitle: {
      [theme.breakpoints.down('sm')]: {
        marginTop: theme.spacing(1)
      }
    },
    actionMenu: {
      position: 'absolute !important',
      right: theme.spacing(0.25),
      bottom: theme.spacing(1),
      fontSize: `${theme.mixins.pxToRem(13)} !important`,
      color: `${theme.palette.text.hint} !important`
    }
  })
);
