import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        margin: theme.spacing(2)
      },
      paper: {
        height: 400
      },
      appIcon: {
        height: 25,
        width: 25
      }
    }),
  {
    name: 'AdminBrowseInstalledApps'
  }
);

export default useStyles;
