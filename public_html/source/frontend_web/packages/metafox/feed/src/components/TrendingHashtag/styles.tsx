import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      title: {
        '& a': {
          color: theme.palette.text.primary,
          '&:hover': {
            textDecoration: 'underline'
          }
        }
      }
    }),
  {
    name: 'Hashtag_TrendingItem'
  }
);
