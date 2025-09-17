import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        marginLeft: '0 !important',
        marginRight: '0 !important',
        marginBottom: theme.spacing(2)
      },
      label: {
        marginLeft: '0 !important',
        marginRight: '0 !important'
      },
      fullWidth: {
        width: '100%',
        justifyContent: 'space-between'
      },
      switch: {}
    }),
  { name: 'SwitchField' }
);
