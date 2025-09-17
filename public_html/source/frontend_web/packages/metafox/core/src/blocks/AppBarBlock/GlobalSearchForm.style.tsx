import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        width: 320,
        position: 'absolute',
        left: '50%',
        transform: 'translateX(-50%)',
        top: 0,
        paddingTop: 13,
        overflow: 'hidden',
        zIndex: theme.zIndex.appBar
      },
      rootOpen: {
        background: theme.palette.background.paper,
        borderBottomLeftRadius: theme.shape.borderRadius,
        borderBottomRightRadius: theme.shape.borderRadius,
        boxShadow: theme.shadows[8],
        '& $searchIcon': {
          color: theme.palette.primary.main
        },
        '& $resultWrapper': {
          display: 'block'
        }
      },
      form: {
        width: 284,
        position: 'relative',
        background: theme.palette.action.hover,
        borderRadius: '999px',
        height: theme.spacing(4),
        margin: theme.spacing(0, 2, 0, 2),
        '& .MuiOutlinedInput-notchedOutline': {
          border: 'none'
        },
        '& .MuiInputBase-input': {
          height: theme.spacing(4),
          boxSizing: 'border-box'
        }
      },
      formFocused: {
        border: theme.mixins.border('primary'),
        borderColor: theme.palette.primary.main,
        background: 'none'
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
        padding: `${theme.spacing(0.5, 1, 0.75, 1)} !important`,
        boxSizing: 'border-box',
        // vertical padding + font size from searchIcon
        paddingLeft: '30px',
        transition: 'all .2s ease',
        width: '100%',
        height: '32px'
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
        fontWeight: theme.typography.fontWeightBold
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
        display: 'none'
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
        padding: theme.spacing(1.2, 2),
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
        fontWeight: 'normal !important'
      },
      recentItem: {
        padding: theme.spacing(1, 2),
        display: 'flex',
        alignItems: 'center',
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(15),
        cursor: 'pointer',
        '& + $recentItem': {
          borderTop: 'solid 1px',
          borderTopColor: theme.palette.border?.secondary
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
      searchItem: {
        display: 'flex',
        flexDirection: 'row',
        padding: theme.spacing(1.5),
        color: theme.palette.text.primary,
        alignItems: 'center',
        borderTop: 'solid 1px',
        borderTopColor: theme.palette.border?.secondary
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
      divider: {
        margin: `${theme.spacing(0, 2, 0, 1)} !important`,
        height: '32px !important'
      }
    }),
  { name: 'AppBar' }
);

export default useStyles;
