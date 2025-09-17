import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles((theme: Theme) =>
  createStyles({
    dialog: {},
    dialogContent: {
      height: 370,
      padding: `${theme.spacing(0, 0, 2, 0)} !important`
    },
    inputStage: {
      padding: theme.spacing(2)
    },
    btnCancel: {
      minWidth: theme.spacing(3),
      padding: 0,
      marginRight: theme.spacing(1),
      color: theme.palette.text.secondary
    },
    searchIcon: {
      color: theme.palette.text.secondary
    },
    dialogTitle: {},
    dialogContentTitle: {
      color: theme.palette.text.secondary,
      padding: theme.spacing(0.5, 0, 0.5, 2)
    },
    mapStage: {}
  })
);
