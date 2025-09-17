import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      imgWraper: {
        height: 300,
        overflow: 'hidden'
      },
      wrapper: {
        display: 'block'
      },
      coverPhotoWrapper: {
        display: 'block',
        height: 320,
        overflow: 'hidden',
        position: 'relative'
      },
      coverPhotoInner: {
        display: 'block',
        position: 'absolute',
        left: 0,
        top: 0,
        right: 0,
        bottom: 0,
        '& img': {
          position: 'relative',
          width: '100%',
          objectFit: 'cover'
        }
      },
      userInfoContainer: {
        backgroundColor: theme.mixins.backgroundColor('paper'),
        [theme.breakpoints.down('sm')]: {
          flexFlow: 'column',
          width: '100%',
          alignItems: 'center'
        }
      },
      userInfo: {
        flex: 1,
        minWidth: 0,
        [theme.breakpoints.down('sm')]: {
          flexFlow: 'column',
          width: '100%',
          alignItems: 'center',
          marginBottom: theme.spacing(2)
        }
      },
      wrapperButtonInline: {
        display: 'flex',
        justifyContent: ' center',
        alignItems: 'stretch',
        '& button': {
          marginLeft: theme.spacing(1),
          textTransform: 'capitalize',
          fontWeight: 'bold',
          whiteSpace: 'nowrap',
          borderRadius: theme.spacing(0.5),
          fontSize: theme.mixins.pxToRem(13),
          padding: theme.spacing(0.5, 1.25),
          marginBottom: theme.spacing(1),
          minWidth: theme.spacing(4),
          height: theme.spacing(4),
          '& .ico': {
            fontSize: theme.mixins.pxToRem(13)
          }
        },
        [theme.breakpoints.down('sm')]: {
          flexFlow: 'row wrap',
          padding: theme.spacing(0.5, 0),
          '& button': {
            marginLeft: 0,
            marginRight: theme.spacing(1)
          }
        }
      },
      profileActionMenu: {
        padding: `${theme.spacing(0.5, 1.25)} !important`,
        minWidth: theme.spacing(4),
        height: theme.spacing(4)
      },
      title: {
        fontWeight: 'bold',
        fontSize: theme.mixins.pxToRem(24),
        color: theme.palette.text.primary,
        margin: 0,
        padding: 0
      },
      summary: {},
      avatarWrapper: {
        marginTop: '-96px',
        marginRight: theme.spacing(3),
        position: 'relative',
        [theme.breakpoints.down('sm')]: {
          marginRight: 0
        }
      },
      userAvatar: {
        width: '164px',
        height: '164px',
        border: '2px solid',
        borderColor: theme.palette.background.paper
      },
      profileUserWrapper: {},
      wrapperMenu: {
        backgroundColor: theme.mixins.backgroundColor('paper'),
        display: 'flex',
        paddingTop: theme.spacing(1),
        borderTop: 'solid 1px',
        borderTopColor: theme.palette.border?.secondary,
        borderBottomLeftRadius: theme.shape.borderRadius,
        borderBottomRightRadius: theme.shape.borderRadius,
        // overflow: 'hidden',
        height: theme.spacing(7.75),
        '& .MuiProfileMenu-tabItem': {
          padding: theme.spacing(2)
        }
      },
      profileMenu: {
        flex: 1,
        minWidth: 0
      },
      profileHeaderContainer: {}
    }),
  { name: 'UserProfileHeader' }
);
