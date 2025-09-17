import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      avatarWrapper: {
        marginTop: -96,
        marginRight: theme.spacing(3),
        position: 'relative',
        [theme.breakpoints.down('sm')]: {
          marginRight: 0,
          marginBottom: theme.spacing(1)
        }
      },
      userAvatar: {
        border: '4px solid',
        borderColor: theme.palette.background.paper,
        fontSize: theme.mixins.pxToRem(32),
        '&:before': {
          content: "''",
          position: 'absolute',
          width: '100%',
          height: '100%',
          borderRadius: '50%',
          borderWidth: 'thin',
          borderStyle: 'solid',
          borderColor: theme.palette.border.secondary
        },
        [theme.breakpoints.down('sm')]: {
          border: '2px solid',
          borderColor: theme.palette.background.paper
        }
      },
      btnEditAvatar: {
        textTransform: 'capitalize',
        position: 'absolute',
        top: theme.spacing(1),
        right: theme.spacing(1),
        minWidth: 32,
        height: 32,
        borderRadius: '100% !important'
      },
      iconEdit: {
        fontSize: theme.mixins.pxToRem(13)
      }
    }),
  { name: 'ProfileHeaderAvatar' }
);
