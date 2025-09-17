import { Button, Theme } from '@mui/material';
import { createStyles, withStyles } from '@mui/styles';

const styles = (theme: Theme) =>
  createStyles({
    root: {},
    colorInherit: {},
    contained: {
      backgroundColor: 'rgba(255,255,255,0.96)',
      '&:hover': {
        backgroundColor: 'rgba(255,255,255,1.0)'
      }
    },
    startIcon: {
      '&> *:first-of-type': {
        fontSize: '13px'
      }
    }
  });

export default withStyles(styles)(Button);
