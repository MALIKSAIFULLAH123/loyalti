import { createStyles } from '@mui/styles';
import { Theme } from '@mui/material';

const styles = (theme: Theme) =>
  createStyles({
    root: {
      width: '100%',
      height: '40px',
      margin: 0,
      padding: '13px 16px 11px 16px',
      borderRadius: '20px',
      border: theme.mixins.border('secondary'),
      backgroundColor: theme.mixins.backgroundColor('default')
    },
    input: {
      textIndent: 8
    }
  });

export default styles;
