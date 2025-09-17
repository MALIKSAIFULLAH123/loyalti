import { Theme } from '@mui/material';
import { createStyles } from '@mui/styles';

const styles = (theme: Theme) =>
  createStyles({
    dialog: {
      '& .MuiDialog-paper': {
        maxWidth: '720px !important'
      }
    },
    titleWrapper: {
      height: 50,
      display: 'flex',
      alignItems: 'center',
      fontWeight: theme.typography.fontWeightBold
    },
    btnCancel: {
      minWidth: theme.spacing(3),
      padding: 0,
      marginRight: theme.spacing(1),
      color: theme.palette.text.secondary
    },
    btnDone: {
      marginLeft: 'auto',
      fontWeight: theme.typography.fontWeightBold
    },
    dialogContent: {
      display: 'flex',
      flexDirection: 'column',
      maxHeight: 'unset !important'
    },
    emptyContentWrapper: {
      padding: theme.spacing(2, 2, 2, 2)
    },
    dialogActions: {},
    searchInput: {
      padding: 16
    },
    chipContainer: {
      padding: theme.spacing(0, 2),
      display: 'flex',
      flexWrap: 'wrap',
      '& > div': {
        margin: theme.spacing(0, 1, 1, 0)
      }
    }
  });

export default styles;
