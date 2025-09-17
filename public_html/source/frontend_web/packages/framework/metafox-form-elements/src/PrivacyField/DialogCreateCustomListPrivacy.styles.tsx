import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      actionWrapper: {
        marginBottom: theme.spacing(2),
        paddingRight: theme.spacing(2),
        '& .MuiButton-root': {
          marginLeft: theme.spacing(1.5)
        }
      },
      suggestionsWrapper: {
        position: 'fixed',
        zIndex: 1,
        backgroundColor: theme.mixins.backgroundColor('paper'),
        padding: theme.spacing(2),
        borderRadius: '4px',
        boxShadow: theme.shadows[2]
      },
      userWrapper: {
        margin: theme.spacing(1, 0),
        minWidth: '385px'
      },
      userOuter: {
        display: 'inline-flex',
        alignItems: 'center',
        padding: theme.spacing(0.5),
        color: theme.palette.primary.main,
        border: theme.mixins.border('primary'),
        marginRight: theme.spacing(0.5),
        marginBottom: theme.spacing(0.5),
        '&:hover': {
          background: theme.palette.primary.main,
          color: '#fff',
          '& $removeBtn': {
            color: '#fff'
          }
        }
      },
      removeBtn: {
        color: theme.palette.primary.main,
        fontSize: 11,
        margin: theme.spacing(0, 0.5, 0, 1),
        cursor: 'pointer'
      },
      name: {
        fontSize: theme.mixins.pxToRem(13)
      }
    }),
  { name: 'DialogCreateCustomListPrivacy' }
);
