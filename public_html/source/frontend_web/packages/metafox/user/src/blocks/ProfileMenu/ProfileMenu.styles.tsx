import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'block',
        height: 40,
        overflow: 'hidden',
        maxHeight: 40,
        overFlow: 'hidden'
      },
      tabItem: {
        minHeight: 40,
        borderBottom: '2px solid transparent',
        marginRight: theme.spacing(2),
        padding: theme.spacing(1, 2),
        height: '100%',
        display: 'inline-block',
        alignItems: 'center',
        justifyContent: 'center',
        float: 'left',
        textDecoration: 'none',
        fontSize: theme.mixins.pxToRem(15),
        fontWeight: 'bold',
        color: theme.palette.text.secondary,
        '&:hover': {
          textDecoration: 'none'
        }
      },
      tabItemActive: {
        borderColor: theme.palette.primary.main,
        color: `${theme.palette.primary.main} !important`
      },
      tabItemMore: {
        minWidth: 60,
        cursor: 'pointer'
      },
      menuItem: {
        minHeight: '40px',
        display: 'block',
        padding: theme.spacing(1, 2),
        alignItems: 'center',
        justifyContent: 'center',
        textDecoration: 'none',
        fontSize: theme.mixins.pxToRem(15),
        fontWeight: theme.typography.fontWeightMedium,
        color: `${theme.palette.text.secondary} !important`,
        '&:hover': {
          textDecoration: 'none'
        }
      },
      menuItemActive: {
        color: theme.palette.primary.main
      },
      secondMenu: {
        listStyle: 'none none outside',
        margin: 0,
        padding: 0,
        height: 40,
        display: 'inline-block'
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
          textDecoration: 'none !important'
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
        padding: theme.spacing(1, 0),
        '& a:hover': {
          backgroundColor: theme.palette.action.hover
        }
      }
    }),
  { name: 'MuiProfileMenu' }
);

export default useStyles;
