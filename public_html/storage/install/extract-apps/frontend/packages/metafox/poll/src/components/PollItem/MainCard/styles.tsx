import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles((theme: Theme) =>
  createStyles({
    itemMinor: {
      color: theme.palette.text.secondary,
      fontSize: 13,
      lineHeight: 1.2
    },
    itemStatistic: {
      marginTop: theme.spacing(2),
      display: 'flex'
    },
    itemFlag: {
      position: 'absolute',
      right: -2,
      bottom: theme.spacing(1.5),
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'flex-end',
      [theme.breakpoints.down('sm')]: {
        bottom: 0
      }
    }
  })
);
