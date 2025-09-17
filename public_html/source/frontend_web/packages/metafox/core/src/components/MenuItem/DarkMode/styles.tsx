import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        width: '100%',
        display: 'flex',
        justifyContent: 'space-between'
      },
      iconStyle: {
        paddingRight: theme.spacing(1)
      },
      toggle: {}
    }),
  { name: 'MenuItem' }
);

export default useStyles;
