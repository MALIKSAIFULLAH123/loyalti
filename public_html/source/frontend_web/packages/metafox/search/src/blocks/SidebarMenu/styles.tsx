import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      searchResults: {
        padding: theme.spacing(2)
      },
      menuList: {
        listStyle: 'none',
        padding: theme.spacing(0, 2),
        margin: 0,
        borderTop: 'solid 1px',
        borderTopColor: theme.palette.border?.secondary,
        paddingTop: theme.spacing(1)
      },
      text: {
        flexGrow: 1
      },
      colapseIcon: {},
      icon: {
        color: '#fff',
        backgroundColor: theme.palette.primary.main,
        borderRadius: '32px',
        width: '32px',
        height: '32px',
        textAlign: 'center',
        marginRight: theme.spacing(2),
        lineHeight: '32px'
      },
      dropDownIcon: {},
      menuItemAvatar: {},
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
      menuItem: {
        marginLeft: -theme.spacing(2),
        marginRight: -theme.spacing(2),
        paddingLeft: theme.spacing(3),
        '&:hover': {
          backgroundColor: theme.palette.action.hover
        },
        '& + $menuItem': {
          marginTop: theme.spacing(1)
        }
      },
      menuItemLink: {
        color: theme.palette.text.primary,
        display: 'block',
        padding: theme.spacing(1.5),
        fontSize: theme.mixins.pxToRem(15)
      },
      activeMenuItem: {
        backgroundColor: '#f5f5f5',
        paddingLeft: theme.spacing(3) - 4,
        borderLeft: '4px solid',
        borderColor: theme.palette.primary.main,
        '& $menuItemLink': {
          color: theme.palette.primary.main
        }
      },
      menuItemButton: {
        marginTop: theme.spacing(2)
      },
      titleSearch: {
        fontSize: theme.mixins.pxToRem(24),
        color: theme.palette.text.primary,
        fontWeight: theme.typography.fontWeightBold
      },
      querySearch: {}
    }),
  { name: 'MuiGlobalSearchFilterBlock' }
);

export default useStyles;
