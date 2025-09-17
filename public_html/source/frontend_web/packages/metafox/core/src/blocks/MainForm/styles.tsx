import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      formWrapper: {
        height: '100%',
        '& > form': {
          height: '100%',
          flexWrap: 'nowrap',
          display: 'flex',
          flexDirection: 'column'
        }
      }
    }),
  { name: 'MainForm' }
);

export default useStyles;
