import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export default makeStyles((theme: Theme) =>
  createStyles({
    root: {},
    vertical: {},
    horizontal: {
      display: 'flex',
      flexWrap: 'wrap',
      '& > div': {
        minHeight: theme.spacing(5),
        paddingRight: theme.spacing(1)
      }
    }
  })
);
