import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      disableGutter: {
        padding: 0
      }
    }),
  { name: 'TabMenuBlock' }
);

export default useStyles;
