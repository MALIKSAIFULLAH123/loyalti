import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        position: 'relative',
        textDecoration: 'none',
        display: 'block',
        borderBottom: theme.mixins.border('secondary'),
        '&:hover': {
          backgroundColor: theme.palette.action.hover
        }
      },
      itemOuter: {
        height: '100%'
      },
      itemInner: {
        height: '100%',
        position: 'relative',
        padding: theme.spacing(1),
        alignItems: 'center',
        display: 'flex',
        flexDirection: 'row',
        overflow: 'hidden'
      },
      itemMainContent: {
        display: 'flex',
        alignItems: 'center'
      },
      itemSmallInner: {
        display: 'flex',
        padding: theme.spacing(2),
        justifyContent: 'space-between',
        alignItems: 'center',
        borderRadius: theme.shape.borderRadius
      },
      actionContent: {
        fontWeight: `${theme.typography.fontWeightMedium} !important`
      },
      itemSmallRequestInner: {
        display: 'flex',
        padding: theme.spacing(2),
        borderRadius: theme.shape.borderRadius
      },
      itemMedia: {
        overflow: 'hidden',
        position: 'relative'
      },
      itemSmallMedia: {
        transition: 'all 300ms ease',
        borderRadius: '100%',
        position: 'relative'
      },
      itemReactSmall: {
        position: 'absolute',
        width: '15px',
        height: '15px',
        right: 0,
        bottom: 0
      },
      imgSmallReactionIcon: { width: '100%' },
      imgWrapper: {
        margin: 'auto'
      },
      imgSmallWrapper: {
        borderRadius: '100%'
      },
      userInfo: {
        flex: 1,
        overflow: 'hidden',
        paddingLeft: theme.spacing(2),
        paddingRight: theme.spacing(2),
        flexBasis: 'auto'
      },
      userSmallInfo: {
        marginLeft: theme.spacing(1.5),
        flex: 1,
        overflow: 'hidden',
        paddingRight: theme.spacing(1)
      },
      userSmallInfoRequest: {
        marginLeft: theme.spacing(1.5),
        flex: 1,
        overflow: 'hidden'
      },
      userTitle: {
        color: theme.palette.text.primary,
        fontSize: '1.125rem',
        marginBottom: theme.spacing(1),
        transition: 'all 300ms ease',
        whiteSpace: 'nowrap',
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        fontWeight: theme.typography.fontWeightBold
      },
      userSmallTitle: {
        color: theme.palette.text.primary,
        fontSize: theme.mixins.pxToRem(15),
        whiteSpace: 'nowrap',
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        fontWeight: theme.typography.fontWeightBold
      },
      friendInfo: {
        fontSize: theme.mixins.pxToRem(13),
        color: theme.palette.text.secondary
      },
      friendSmallInfo: {
        fontSize: theme.mixins.pxToRem(13),
        color: theme.palette.text.secondary,
        marginTop: theme.spacing(0.5),
        display: 'inline-block'
      },
      userLocation: {
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(13),
        whiteSpace: 'nowrap',
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        marginBottom: theme.spacing(1)
      },
      userJoined: {
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(13),
        whiteSpace: 'nowrap',
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        marginBottom: theme.spacing(1)
      },
      friendActions: {
        position: 'relative',
        display: 'flex'
      },
      friendActionsDropdown: {
        boxShadow: 'none',
        padding: 0,
        marginLeft: theme.spacing(1)
      },
      iconButton: {
        border: '1px solid',
        width: '40px',
        height: '40px',
        borderRadius: theme.spacing(0.5),
        display: 'flex',
        alignItems: 'center',
        color: theme.palette.primary.main,
        justifyContent: 'center'
      },
      button: {
        boxShadow: 'none',
        borderRadius: theme.spacing(0.5),
        fontSize: '1rem',
        textTransform: 'capitalize'
      },
      friendRequestActions: {
        display: 'flex',
        marginTop: theme.spacing(1.5)
      },
      requestOption: {
        textTransform: 'capitalize',
        fontSize: theme.mixins.pxToRem(15),
        fontWeight: 'bold',
        flexGrow: 1,
        '& + $requestOption': {
          marginLeft: theme.spacing(1)
        }
      },
      mutualFriend: {
        marginRight: theme.spacing(0.5)
      },
      btnAddFriend: {
        textTransform: 'capitalize',
        fontSize: theme.mixins.pxToRem(13),
        fontWeight: theme.typography.fontWeightBold,
        padding: theme.spacing(0.25, 1.5),
        minWidth: 'inherit'
      }
    }),
  { name: 'ReactedUserItemView' }
);
