import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

// eslint-disable-next-line @typescript-eslint/no-unused-vars
export default makeStyles((theme: Theme) =>
  createStyles({
    options: {},
    subOption: {
      paddingLeft: 32
    },
    formLabel: {
      zIndex: 1
    },
    'formLabel-outlined': {
      backgroundColor: theme.palette.background.paper,
      padding: theme.spacing(0, 1),
      marginLeft: theme.spacing(-1)
    }
  })
);
