import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      siteWide: {},
      body: {
        display: 'flex',
        flexDirection: 'row'
      },
      backdrop: {
        zIndex: theme.zIndex.appBar + 1
      },
      content: {
        flex: 1,
        overflowX: 'hidden'
      }
    }),
  {
    name: 'AdminCP'
  }
);

export default useStyles;
