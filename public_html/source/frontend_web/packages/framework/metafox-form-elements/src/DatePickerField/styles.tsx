import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        padding: theme.spacing(2, 0, 1)
      },
      iconCalendar: {
        fontSize: 20
      }
    }),
  {
    name: 'DatePickerField'
  }
);
