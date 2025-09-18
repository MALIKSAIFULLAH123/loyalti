import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      profileUserWrapper: {
        padding: theme.spacing(2),
        background: theme.palette.background.paper,
        borderBottom: theme.mixins.border('secondary'),
        display: 'flex'
      },
      name: {},
      type: {
        marginTop: theme.spacing(1)
      },
      btn: {
        marginLeft: 'auto'
      },
      menu: {
        marginBottom: theme.spacing(2),
        background: theme.palette.background.paper,
        borderBottomLeftRadius: theme.shape.borderRadius,
        borderBottomRightRadius: theme.shape.borderRadius,
        height: theme.spacing(7.5),
        display: 'flex',
        alignItems: 'center',
        '& > .MuiSkeleton-root': {
          padding: theme.spacing(2),
          marginLeft: theme.spacing(2)
        }
      },
      btnGroups: {
        display: 'flex',
        marginLeft: 'auto',
        marginRight: theme.spacing(2),
        '& .MuiSkeleton-root': {
          padding: theme.spacing(1),
          marginLeft: theme.spacing(1)
        }
      }
    }),
  { name: 'GroupProfileHeaderSkeleton' }
);
