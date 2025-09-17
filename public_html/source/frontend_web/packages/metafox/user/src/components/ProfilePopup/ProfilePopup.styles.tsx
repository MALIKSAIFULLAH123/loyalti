import { Theme } from '@mui/material';
import { alpha } from '@mui/system/colorManipulator';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      paper: {
        width: 372,
        overflow: 'hidden',
        padding: theme.spacing(2),
        boxShadow: theme.shadows[12]
      },
      header: {
        display: 'flex',
        alignItems: 'center'
      },
      avatar: {
        width: 80,
        height: 80
      },
      profileLink: {
        fontSize: theme.typography.body1.fontSize,
        lineHeight: 2.41,
        fontWeight: 'bold',
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap',
        maxWidth: '100%',
        '& a': {
          color: theme.palette.text.primary
        }
      },
      location: {
        marginTop: theme.spacing(1),
        color: alpha(theme.palette.text.secondary, 0.9),
        fontSize: theme.typography.body2.fontSize,
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap',
        maxWidth: '100%',
        '& .ico': {
          fontSize: theme.mixins.pxToRem(15),
          marginRight: theme.spacing(1),
          marginTop: 1
        }
      },
      friends: {
        display: 'flex',
        alignItems: 'center',
        color: alpha(theme.palette.text.secondary, 0.9),
        marginTop: theme.spacing(1)
      },
      groupAvatar: {
        marginRight: theme.spacing(1),
        paddingLeft: theme.spacing(1),
        display: 'flex',
        flexDirection: 'row-reverse'
      },
      userAvatarFriend: {
        width: 24,
        height: 24,
        marginLeft: theme.spacing(-1)
      },
      buttonWrapper: {
        marginTop: theme.spacing(1.5)
      },
      sendRequest: {
        height: theme.spacing(3),
        lineHeight: theme.spacing(3),
        borderRadius: '4px',
        backgroundColor: theme.palette.background.default,
        color: theme.palette.text.secondary,
        fontSize: theme.typography.body2.fontSize,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        margin: theme.spacing(2, 0, 0.5)
      },
      isOwner: {
        display: 'none'
      },
      actionsDropdown: {
        marginLeft: theme.spacing(0.5),
        boxShadow: 'none',
        padding: 0,
        borderRadius: '4px !important',
        border: '1px solid !important',
        borderColor: theme.palette.border.primary,
        flex: 'none !important'
      },
      buttonDisplay: {
        flex: 1,
        padding: '0px !important'
      }
    }),
  { name: 'MuiProfilePopup' }
);

export default useStyles;
