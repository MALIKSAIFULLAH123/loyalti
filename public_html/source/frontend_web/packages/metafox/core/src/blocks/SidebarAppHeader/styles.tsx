import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        padding: theme.spacing(2, 2, 0, 2)
      },
      header: {
        display: 'flex',
        alignItems: 'center',
        color: theme.palette.primary.main
      },
      title: {
        fontSize: theme.mixins.pxToRem(24),
        fontWeight: theme.typography.fontWeightBold
      },
      text: {
        flexGrow: 1
      },
      icon: {
        fontSize: theme.mixins.pxToRem(16),
        color: '#fff',
        backgroundColor: theme.palette.primary.main,
        borderRadius: '32px',
        width: '32px',
        height: '32px',
        textAlign: 'center',
        marginRight: theme.spacing(1.5),
        lineHeight: '32px'
      },
      link: {
        color: theme.palette.primary.main,
        '&:hover': {
          textDecoration: 'underline'
        }
      },
      breadcrumbs: {
        display: 'flex',
        alignItems: 'center'
      }
    }),
  { name: 'SideAppHeaderBlock' }
);

export default useStyles;
