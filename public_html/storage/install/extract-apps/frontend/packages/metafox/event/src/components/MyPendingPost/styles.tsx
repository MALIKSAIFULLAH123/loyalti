import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      header: {
        display: 'flex',
        flexDirection: 'row'
      },
      headerInfo: {
        padding: '4px 0',
        flex: 1
      },
      privacyBlock: {
        display: 'flex',
        flexDirection: 'row',
        alignItems: 'center',
        color: theme.palette.text.secondary,
        fontSize: '0.8125rem',
        paddingTop: '0.25em'
      }
    }),
  { name: 'PendingPost' }
);
