import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},

      customTabs: {
        '& .MuiTabs-flexContainer': {
          margin: theme.spacing(1, 1.5, 1, 1.5)
        },
        '& .MuiTab-root': {
          fontSize: theme.spacing(2),
          minHeight: 'auto',
          borderRadius: theme.shape.borderRadius / 2,
          marginRight: '0.25em',
          padding: theme.spacing(0.5),
          fontWeight: theme.typography.fontWeightSemiBold
        }
      }
    }),
  { name: 'Notifications' }
);
