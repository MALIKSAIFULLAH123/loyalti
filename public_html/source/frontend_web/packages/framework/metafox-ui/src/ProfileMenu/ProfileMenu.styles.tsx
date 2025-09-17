import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        minHeight: 40,
        overflow: 'hidden',
        position: 'relative',
        display: 'flex',
        alignItems: 'center'
      },
      mobileRoot: {
        minHeight: 40,
        display: 'flex',
        overflowY: 'auto',
        position: 'relative'
      },
      hiddenMenu: {
        position: 'absolute',
        left: 0,
        top: 0,
        visibility: 'hidden',
        display: 'flex',
        width: '100%'
      },
      visibleMenu: {
        overflow: 'hidden',
        display: 'inline-flex'
      },
      tabItem: {
        minHeight: 40,
        padding: theme.spacing(3.25, 2),
        height: '100%',
        display: 'inline-block',
        alignItems: 'center',
        justifyContent: 'center',
        float: 'left',
        textDecoration: 'none',
        fontSize: '15px',
        fontWeight: 'bold',
        color: `${theme.palette.text.secondary} !important`,
        position: 'relative',
        whiteSpace: 'nowrap',
        '&:hover': {
          textDecoration: 'none',
          [theme.breakpoints.up('sm')]: {
            color: `${theme.palette.primary.main} !important`
          }
        },
        [theme.breakpoints.down('xs')]: {
          padding: `26px ${theme.spacing(1)}px`,
          marginBottom: 0
        }
      },
      tabItemActive: {
        color: `${theme.palette.primary.main} !important`,
        '&:after': {
          content: '""',
          height: 2,
          position: 'absolute',
          left: theme.spacing(2),
          right: theme.spacing(2),
          bottom: 0,
          backgroundColor: theme.palette.primary.main
        }
      },
      tabItemMore: {
        minWidth: 60,
        cursor: 'pointer',
        marginRight: 0,
        whiteSpace: 'nowrap',
        flexGrow: 1,
        display: 'none'
      },
      moreMenu: {
        display: 'block'
      },
      menuItem: {
        minHeight: '40px',
        display: 'block',
        padding: theme.spacing(1, 2),
        alignItems: 'center',
        justifyContent: 'center',
        textDecoration: 'none',
        fontSize: '15px',
        fontWeight: theme.typography.fontWeightMedium,
        color: `${theme.palette.text.secondary} !important`,
        '&:hover': {
          textDecoration: 'none !important'
        }
      },
      menuItemActive: {
        color: `${theme.palette.primary.main} !important`
      },
      secondMenu: {
        listStyle: 'none none outside',
        margin: 0,
        padding: 0,
        display: 'inline-flex'
      },
      invisible: {
        visibility: 'hidden',
        pointerEvents: 'none'
      },
      profileMenuItemMore: {
        margin: 0,
        borderColor: 'transparent'
      },
      menuLink: {
        textDecoration: 'none',
        fontSize: '15px',
        color: theme.palette.text.secondary,
        '&:hover': {
          textDecoration: 'none'
        }
      },
      menuItemMore: {
        margin: 0,
        '&:not($menuItemMoreShow)': {
          opacity: 0,
          visibility: 'hidden'
        }
      },
      menuItemMoreShow: {},
      iconDropdown: {
        marginLeft: theme.spacing(1)
      },
      moreBtn: {
        display: 'inline-flex',
        alignItems: 'center'
      },
      popperMenu: {
        width: 240,
        boxShadow: theme.shadows[20],
        borderRadius: theme.shape.borderRadius,
        overflow: 'hidden',
        backgroundColor: theme.palette.background.paper,
        zIndex: 1300,
        padding: theme.spacing(1, 0),
        '& a:hover': {
          backgroundColor: theme.palette.action.hover
        }
      }
    }),
  { name: 'MuiProfileMenu' }
);

export default useStyles;
