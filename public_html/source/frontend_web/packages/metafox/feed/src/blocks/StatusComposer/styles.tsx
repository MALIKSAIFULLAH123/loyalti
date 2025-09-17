import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        backgroundColor: theme.palette.background.paper
      },
      composerWrapper: {
        display: 'flex',
        width: '100%',
        [theme.breakpoints.down('sm')]: {
          display: 'block',
          width: '100%'
        }
      },
      statusToolbar: {
        display: 'flex',
        marginTop: theme.spacing(1),
        marginLeft: theme.spacing(1.5),
        [theme.breakpoints.down('sm')]: {
          marginLeft: theme.spacing(0)
        }
      },
      statusToolbarExpanded: {
        display: 'flex',
        borderTop: 'solid 1px',
        borderTopColor: theme.palette.border?.secondary,
        marginTop: theme.spacing(2),
        marginLeft: theme.spacing(8),
        paddingTop: theme.spacing(1)
      },
      avatarStage: {
        marginRight: theme.spacing(1.5)
      },
      inputStage: {
        display: 'flex',
        justifyContent: 'flex-start',
        flex: 1,
        backgroundColor: theme.palette.action.hover,
        height: theme.spacing(6),
        alignItems: 'center',
        borderRadius: 24,
        paddingLeft: 24,
        cursor: 'pointer',
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(15),
        fontWeight: theme.typography.fontWeightRegular,
        letterSpacing: 0,
        [theme.breakpoints.down('sm')]: {
          marginTop: theme.mixins.pxToRem(4),
          backgroundColor: 'unset',
          height: 'unset',
          padding: '0 !important'
        }
      }
    }),
  {
    name: 'StatusComposer'
  }
);

export default useStyles;
