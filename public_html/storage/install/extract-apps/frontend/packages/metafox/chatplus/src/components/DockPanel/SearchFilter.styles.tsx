import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      optionLabel: {
        width: '32px',
        height: '32px',
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        margin: '0',
        color: '#a2a2a2'
      },
      optionChecked: {
        color: theme.palette.primary.main
      },
      optionInput: {
        display: 'none'
      },
      optionIcon: {
        display: 'inline-flex',
        alignItems: 'center',
        marginTop: '1px',
        height: '14px',
        marginRight: '4px'
      }
    }),
  { name: 'ChatDockSearchFilter' }
);
