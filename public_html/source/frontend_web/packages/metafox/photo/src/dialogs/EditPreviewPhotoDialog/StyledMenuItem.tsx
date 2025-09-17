import { Button, Theme } from '@mui/material';
import { createStyles, withStyles } from '@mui/styles';

const styles = (theme: Theme) =>
  createStyles({
    root: {
      fontSize: '1rem',
      justifyContent: 'flex-start !important',
      marginBottom: '12px !important',
      '&.Mui-disabled': {
        '& .MuiButton-startIcon': {
          borderColor: `${theme.palette.action.disabled} !important`
        }
      }
    },
    startIcon: {
      width: '32px',
      height: '32px',
      fontSize: '15px',
      borderRadius: '50%',
      alignItems: 'center',
      textAlign: 'center',
      lineHeight: '32px',
      justifyContent: 'center',
      border: `1px solid ${theme.palette.primary.main}`
    },
    label: {}
  });

export default withStyles(styles)(Button);
