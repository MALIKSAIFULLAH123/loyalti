import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      blockHeader: {
        height: theme.appBarHeight.normal
      },
      rootHeader: {
        height: theme.appBarHeight.normal ?? 58,
        boxShadow: 'none',
        borderBottom: 'solid 1px rgba(0, 0, 0, 0.1)',
        '& .MuiToolbar-root': {
          minHeight: 0,
          maxHeight: theme.appBarHeight.normal ?? 58
        }
      },
      isGuest: {
        '& .MuiToolbar-gutters': {
          paddingLeft: theme.spacing(2.5),
          paddingRight: theme.spacing(2.5)
        }
      },
      leftPane: {
        flex: 1,
        display: 'flex',
        flexDirection: 'row'
      },
      rightPane: {
        display: 'flex',
        flexDirection: 'row',
        flex: 1,
        justifyContent: 'flex-end'
      },
      centerPane: {
        display: 'flex',
        justifyContent: 'center'
      },
      fixedAwareBottom: {
        position: 'relative',
        height: theme.appBarHeight.normal,
        backgroundColor: 'transparent'
      },
      left: {
        display: 'flex',
        alignItems: 'center',
        minHeight: '40px',
        paddingLeft: theme.spacing(2.5)
      },
      middle: {},
      right: {
        paddingRight: theme.spacing(2.5)
      },
      logo: {
        height: '32px',
        display: 'inline-block'
      },
      shortcutLink: {
        margin: theme.spacing('auto', 1),
        padding: theme.spacing(0.5, 0),
        color: theme.palette.text.secondary,
        flex: 1,
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        height: '60px',
        borderBottom: '2px solid transparent',
        borderTop: '2px solid transparent',
        '&:hover $tabLink': {
          background: theme.palette.background.default
        }
      },
      menuButton: {
        padding: theme.spacing(0.5, 0),
        flex: 1,
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        '&:hover $tabLink': {
          background: theme.palette.background.default
        }
      },
      smallMenuButton: {
        width: theme.spacing(5),
        height: theme.spacing(5),
        cursor: 'pointer',
        marginLeft: theme.spacing(0.5),
        color: theme.palette.text.secondary,
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize: theme.mixins.pxToRem(16),
        transition: 'all 0.1s',
        '& .MuiBadge-badge': {
          marginTop: 0
        },
        '&:hover': {
          backgroundColor: theme.palette.action.selected,
          borderRadius: '50%'
        }
      },
      activeMenu: {
        backgroundColor: theme.palette.action.selected,
        borderRadius: '50%',
        '& $smallMenuIcon': {
          color: theme.palette.primary.main
        }
      },
      menuButtonActive: {
        backgroundColor: theme.palette.action.selected,
        borderRadius: '50%',
        '& $smallMenuIcon': {
          color: theme.palette.primary.main
        }
      },
      smallMenuIcon: {
        fontSize: theme.mixins.pxToRem(24),
        color: theme.palette.text.secondary,
        '&.ico-caret-down': {
          fontSize: theme.mixins.pxToRem(16)
        }
      },
      menuActive: {
        borderBottom: '2px solid',
        borderBottomColor: theme.palette.primary.main,
        color: theme.palette.primary.main
      },
      menuButtonIcon: {
        fontSize: theme.mixins.pxToRem(28)
      },
      tabLink: {
        display: 'block',
        width: '100%',
        height: '100%',
        textAlign: 'center',
        padding: theme.spacing(1),
        borderRadius: theme.shape.borderRadius
      },
      rightMenu: {
        display: 'flex',
        flexDirection: 'row',
        justifyContent: 'flex-end'
      },
      userAvatarButton: {
        display: 'flex',
        textDecoration: 'none',
        alignItems: 'center',
        '& > .MuiAvatar-root': {
          borderWidth: 'thin',
          borderStyle: 'solid',
          borderColor: theme.palette.border.secondary
        }
      },
      userAvatarSpan: {
        fontSize: theme.mixins.pxToRem(15),
        color: theme.palette.text.primary,
        display: 'inline-block',
        marginLeft: theme.spacing(1),
        fontWeight: 'bold',
        borderBottom: 'solid 1px',
        borderBottomColor: 'transparent'
      },
      userAvatar: {
        width: '32px',
        height: '32px',
        fontSize: '12px'
      },
      title: {
        flexGrow: 1,
        display: 'none',
        [theme.breakpoints.up('sm')]: {
          display: 'block'
        }
      },
      search: {
        position: 'relative',
        background: theme.palette.action.selected,
        borderRadius: '999px',
        height: theme.spacing(4),
        marginLeft: 0,
        width: '100%',
        [theme.breakpoints.up('sm')]: {
          width: '288px'
        },
        '& .MuiOutlinedInput-notchedOutline': {
          border: 'none'
        },
        '& #global-search': {
          marginLeft: '20px'
        }
      },
      searchFocused: {
        border: theme.mixins.border('primary'),
        background: 'white'
      },
      searchIcon: {
        color: theme.palette.text.secondary,
        transition: 'all .2s ease',
        cursor: 'pointer'
      },
      inputRoot: {
        color: `${theme.palette.text.secondary} !important`,
        width: '100%'
      },
      inputInput: {
        padding: theme.spacing(1, 2, 1, 0),
        boxSizing: 'border-box',
        // vertical padding + font size from searchIcon
        paddingLeft: '30px',
        transition: 'all .2s ease',
        width: '100%',
        height: '30px',
        [theme.breakpoints.up('sm')]: {
          maxWidth: '288px',
          width: '100%',
          '&:focus': {
            maxWidth: '288px'
          }
        }
      },
      guestBar: {
        justifyContent: 'space-between'
      },
      textField: {
        marginRight: theme.spacing(1),
        height: theme.spacing(5),
        width: '203px',
        '& input': {
          color: theme.palette.text.secondary
        }
      },
      button: {
        marginRight: theme.spacing(2),
        textTransform: 'capitalize',
        fontWeight: theme.typography.fontWeightBold
      },
      account: {
        textTransform: 'none',
        fontWeight: theme.typography.fontWeightRegular
      },
      searchIconFocused: {
        color: theme.palette.primary.main
      },
      renderOption: {
        position: 'relative',
        width: '100%',
        padding: theme.spacing(1, 0),
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between'
      },
      removeButton: {
        minWidth: '16px',
        cursor: 'pointer',
        height: '26px',
        borderRadius: '26px',
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(14)
      },
      labelItem: {
        flexGrow: 1,
        paddingRight: theme.spacing(1)
      },
      resultWrapper: {
        paddingTop: theme.spacing(1),
        minWidth: '288px',
        zIndex: theme.zIndex.snackbar
      },
      menuList: {
        margin: 0,
        padding: 0
      },
      menuItem: {
        '& + $menuItem': {
          borderTop: 'solid 1px',
          borderTopColor: theme.palette.border?.secondary
        }
      },
      menuItemLink: {
        padding: theme.spacing(1.5),
        display: 'flex',
        flexDirection: 'row',
        alignItems: 'center',
        fontWeight: 'bold',
        color: theme.palette.text.primary,
        transition: 'all .2s',
        '&:hover': {
          backgroundColor: theme.palette.action.selected,
          borderRadius: theme.shape.borderRadius
        }
      },
      menuItemAvatar: {
        width: theme.spacing(6),
        height: theme.spacing(6),
        marginRight: theme.spacing(2)
      },
      menuItemIcon: {
        display: 'inline-block',
        width: theme.spacing(4),
        height: theme.spacing(4),
        lineHeight: '32px',
        backgroundColor: 'rgba(0,0,0,0.1)',
        borderRadius: '50%',
        color: '#828080',
        textAlign: 'center',
        marginRight: theme.spacing(2)
      },
      headerPopup: {
        padding: theme.spacing(1, 1, 1, 2),
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        textTransform: 'capitalize',
        fontWeight: theme.typography.fontWeightBold,
        fontSize: theme.mixins.pxToRem(18)
      },
      headerRecent: {
        borderBottom: '1px solid',
        borderBottomColor: theme.palette.border?.secondary
      },
      clearButton: {
        textTransform: 'capitalize',
        color: theme.palette.primary.main,
        fontWeight: 'normal',
        minWidth: 'inherit'
      },
      recentItem: {
        padding: theme.spacing(1, 1, 1, 2),
        display: 'flex',
        alignItems: 'center',
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(15),
        cursor: 'pointer',
        '& + $recentItem': {
          borderTop: 'solid 1px',
          borderTopColor: theme.palette.border?.secondary
        },
        '&:hover': {
          background: theme.palette.action.selected
        }
      },
      focusedItem: {
        backgroundColor: theme.palette.action.selected
      },
      recentLabel: {
        flexGrow: 1,
        padding: theme.spacing(1, 0),
        overflow: 'hidden',
        whiteSpace: 'nowrap',
        textOverflow: 'ellipsis'
      },
      loadingItem: {
        padding: theme.spacing(2)
      },
      searchItem: {
        display: 'flex',
        flexDirection: 'row',
        padding: theme.spacing(1.5),
        color: theme.palette.text.primary,
        alignItems: 'center',
        '& + $searchItem': {
          borderTop: 'solid 1px',
          borderTopColor: theme.palette.border?.secondary
        },
        '&:hover': {
          background: theme.palette.action.selected
        }
      },
      searchContent: {
        paddingLeft: theme.spacing(1),
        flexGrow: 1,
        overflow: 'hidden'
      },
      searchAvatar: {
        width: 48,
        height: 48
      },
      searchNote: {
        fontSize: theme.mixins.pxToRem(13),
        color: theme.palette.text.secondary,
        fontWeight: 'normal'
      },
      searchTitle: {
        padding: theme.spacing(0.5, 0),
        overflow: 'hidden',
        whiteSpace: 'nowrap',
        textOverflow: 'ellipsis',
        display: 'block',
        maxWidth: '100%',
        fontWeight: theme.typography.fontWeightBold,
        fontSize: theme.mixins.pxToRem(15),
        color: theme.palette.text.primary
      },
      itemName: {
        fontSize: theme.mixins.pxToRem(13),
        color: theme.palette.text.secondary,
        paddingTop: theme.spacing(0.5),
        fontWeight: 'normal'
      },
      noOptions: {
        fontSize: theme.mixins.pxToRem(15),
        padding: theme.spacing(2),
        display: 'block'
      },
      searchMinimize: {
        width: '32px',
        height: '32px',
        transition: 'all .1s',
        transform: 'translateZ(0)',
        '& $searchIcon': {
          position: 'relative',
          pointerEvents: 'inherit'
        },
        '& $inputRoot': {
          position: 'absolute',
          left: 0,
          top: 0,
          width: '32px',
          height: '30px',
          opacity: 0,
          zIndex: 2,
          cursor: 'pointer'
        },
        [theme.breakpoints.down('md')]: {
          // '&$openPopup': {
          //   minWidth: '120px',
          //   maxWidth: '100%'
          // }
        }
      },
      openPopup: {
        height: '32px',
        '& $inputRoot': {
          maxWidth: '237px',
          width: '100%',
          opacity: 1
        }
      },
      searchForm: {
        position: 'absolute',
        left: '50%',
        transform: 'translateX(-50%)',
        top: 0
      },
      menuRefIndex: {
        display: 'inline-flex'
      },
      popper: {
        width: 290,
        maxHeight: '500px',
        boxShadow: theme.shadows[20],
        borderRadius: theme.shape.borderRadius,
        overflow: 'hidden',
        backgroundColor: theme.palette.background.paper,
        marginTop: theme.spacing(-1)
      },
      moreHeaderMenu: {
        position: 'relative',
        marginRight: theme.spacing(1),
        '& $popper': {
          minWidth: 200,
          boxShadow: '0px 2px 30px 0 rgba(0, 0, 0, 0.1)'
        }
      },
      divider: {
        margin: `${theme.spacing(0, 2, 0, 1)} !important`,
        height: '32px !important'
      },
      searchBackdrop: {
        minHeight: '1000000vh',
        backgroundColor: 'rgba(0, 0, 0, 0.5)',
        position: 'fixed',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        marginTop: '-5px',
        zIndex: 1099,
        transition: 'opacity 225ms cubic-bezier(0.4, 0, 0.2, 1) 0ms'
      }
    }),
  { name: 'AppBar' }
);

export default useStyles;
