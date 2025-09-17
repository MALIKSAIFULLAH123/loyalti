import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      title: {
        fontSize: theme.mixins.pxToRem(15),
        fontWeight: theme.typography.fontWeightBold,
        color:
          theme.palette.mode === 'light'
            ? theme.palette.text.secondary
            : theme.palette.text.hint
      },
      setting: {
        fontSize: theme.mixins.pxToRem(16),
        width: theme.spacing(4),
        height: theme.spacing(4),
        borderRadius: '50%',
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        '&:hover': {
          backgroundColor: theme.palette.action.selected,
          cursor: 'pointer'
        }
      },
      menuItem: {
        padding: theme.spacing(0),
        '&:hover': {
          backgroundColor: theme.palette.action.selected,
          borderTopRightRadius: theme.shape.borderRadius,
          borderBottomRightRadius: theme.shape.borderRadius
        }
      },
      menuItemLink: {
        padding: theme.spacing(0.75, 2.5),
        display: 'flex',
        flexDirection: 'row',
        alignItems: 'center',
        fontWeight: 'bold',
        fontSize: theme.typography.body1.fontSize,
        color: theme.palette.text.primary
      },
      menuItemText: {},
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
        backgroundColor: 'rgba(0,0,0,0.1)',
        borderRadius: '50%',
        color: '#828080',
        textAlign: 'center',
        marginRight: theme.spacing(2)
      },
      shortcutModuleName: {
        display: 'block',
        fontSize: theme.mixins.pxToRem(13),
        lineHeight: 1.33,
        fontWeight: theme.typography.fontWeightRegular,
        color: theme.palette.text.secondary,
        marginTop: theme.spacing(0.5)
      }
    }),
  { name: 'ShorcutMenu' }
);

export default useStyles;
