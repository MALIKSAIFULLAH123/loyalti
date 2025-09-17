import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        paddingLeft: 0,
        '& .MuiSelect-root': {
          paddingLeft: theme.spacing(5)
        }
      },
      startIcon: {
        position: 'absolute',
        left: theme.spacing(2)
      }
    }),
  { name: 'PrivacyField' }
);
