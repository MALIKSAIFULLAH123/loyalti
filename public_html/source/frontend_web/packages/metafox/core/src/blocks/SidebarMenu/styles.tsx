import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      transparency: {},
      paper: {},
      menuList: {
        margin: 0,
        padding: 0,
        listStyle: 'none none outside',
        '&$transparency': {
          paddingLeft: theme.spacing(0.5)
        }
      },
      menuItem: {
        borderRadius: theme.shape.borderRadius,
        '&:hover': {
          backgroundColor: theme.palette.action.hover
        }
      },
      menuItemLink: {
        padding: theme.spacing(1.5),
        display: 'flex',
        flexDirection: 'row',
        alignItems: 'center',
        fontSize: theme.mixins.pxToRem(15),
        color: theme.palette.text.primary,
        cursor: 'pointer'
      },
      menuItemText: {
        fontWeight: theme.typography.fontWeightMedium
      },
      menuItemAvatar: {
        width: theme.spacing(4),
        height: theme.spacing(4),
        marginRight: theme.spacing(1.5)
      },
      menuItemIcon: {
        fontSize: theme.mixins.pxToRem(16),
        display: 'inline-block',
        width: theme.spacing(4),
        height: theme.spacing(4),
        lineHeight: '32px',
        backgroundColor:
          theme.palette.mode === 'light' ? '#e0dddd' : theme.palette.grey[500],
        borderRadius: '50%',
        color:
          theme.palette.mode === 'light'
            ? theme.palette.grey[600]
            : theme.palette.grey[700],
        textAlign: 'center',
        '& .ico-home-alt': {
          display: 'block',
          marginTop: theme.spacing(1)
        }
      },
      activeMenuItem: {
        '& a': {
          color:
            theme.palette.mode === 'light'
              ? theme.palette.primary.main
              : theme.palette.text.primary
        },
        '& $menuItemIcon': {
          color:
            theme.palette.mode === 'light'
              ? theme.palette.background.paper
              : 'white',
          backgroundColor: theme.palette.primary.main
        },
        '& $menuItemText': {
          fontWeight: 'bold'
        }
      }
    }),
  { name: 'SidebarMenu' }
);

export default useStyles;
