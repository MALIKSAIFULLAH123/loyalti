import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      iconStyle: {
        paddingRight: theme.spacing(1)
      },
      itemPrimary: {
        '&:hover': {
          backgroundColor: theme.palette.background.default,
          cursor: 'pointer'
        }
      },
      success: {
        '&.ico': {
          color: theme.palette.success.main
        }
      },
      gray: {
        '&.ico': {
          color: theme.palette.grey['500']
        }
      },
      warning: {
        '&.ico': {
          color: theme.palette.warning.main
        }
      },
      danger: {
        '&.ico': {
          color: theme.palette.error.main
        }
      },
      classActiveButton: {
        color: theme.palette.primary.main,
        '& span': {
          fontWeight: theme.typography.fontWeightBold
        }
      }
    }),
  { name: 'MenuItem' }
);

export default useStyles;
