import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        padding: theme.spacing(3, 11.25),
        backgroundColor: theme.layoutSlot.background.paper,
        boxShadow: '0px -2px 1px 0 rgba(0, 0, 0, 0.05)',
        [theme.breakpoints.down('sm')]: {
          padding: theme.spacing(2, 2)
        }
      },
      contentOuter: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between'
      },
      contentInner: {
        flex: 1,
        minWidth: 0
      },
      title: {
        fontSize: theme.mixins.pxToRem(18),
        fontWeight: theme.typography.fontWeightBold,
        color: theme.palette.grey.A700,
        lineHeight: 1,
        margin: 0
      },
      description: {
        fontSize: theme.mixins.pxToRem(13),
        marginTop: theme.spacing(1.5),
        color: theme.palette.grey.A100
      },
      btnConfirm: {
        height: theme.spacing(5),
        marginLeft: `${theme.spacing(8)}!important`,
        fontWeight: theme.typography.fontWeightBold,
        fontSize: theme.mixins.pxToRem(18),
        color: `${theme.palette.grey.A700}!important`,
        borderColor: `${theme.palette.grey.A700}!important`,
        backgroundColor: '#fff!important',
        '&:hover': {
          backgroundColor: `${theme.palette.grey[100]}!important`
        },
        [theme.breakpoints.down('sm')]: {
          marginLeft: `${theme.spacing(2)}!important`
        }
      }
    }),
  { name: 'CookiePolicy' }
);
