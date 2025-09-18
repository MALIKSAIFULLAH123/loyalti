import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      profileUserWrapper: {
        padding: theme.spacing(2),
        background: theme.palette.background.paper,
        borderBottom: theme.mixins.border('secondary')
      },
      userInfo: {
        display: 'flex'
      },
      avatarWrapper: {
        marginTop: theme.spacing(-12.5),
        padding: theme.spacing(0.5),
        borderRadius: '999px',
        background: theme.palette.background.paper
      },
      avatar: {
        width: 160,
        height: 160,
        borderRadius: '999px',
        background: theme.palette.background.default
      },
      name: {
        marginLeft: theme.spacing(3),
        flex: 1,
        minWidth: 0
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
  { name: 'PageProfileHeaderSkeleton' }
);
