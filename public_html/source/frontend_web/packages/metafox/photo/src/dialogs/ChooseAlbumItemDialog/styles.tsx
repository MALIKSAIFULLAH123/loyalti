import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      dialog: {
        '& .MuiDialog-paper': {
          maxWidth: '720px !important'
        }
      },
      dialogTitle: {
        height: 50,
        maxWidth: 720,
        display: 'flex'
      },
      dialogContent: {
        padding: theme.spacing(0, 2, 2, 2),
        maxWidth: 720,
        height: 426,
        overflow: 'hidden'
      },
      scroll: {
        marginTop: theme.spacing(3),
        '& > *:first-of-type': {
          padding: theme.spacing(0.5),
          maxHeight: '350px !important'
        }
      },
      scrollAlbum: {
        marginTop: theme.spacing(2),
        '& > *:first-of-type': {
          padding: theme.spacing(0.5),
          maxHeight: '406px !important'
        }
      },
      btnBack: {
        marginRight: `${theme.spacing(1)} !important`,
        '& .ico': {
          flex: 1
        }
      },
      tab: {
        height: 50,
        fontSize: theme.mixins.pxToRem(15),
        color: theme.palette.text.secondary,
        fontWeight: 'bold',
        borderBottom: '2px solid transparent',
        minWidth: 0,
        padding: 0,
        marginRight: theme.spacing(5),
        '& .Mui-selected': {
          borderBottomColor: theme.palette.primary.main,
          color: theme.palette.primary.main
        }
      }
    }),
  { name: 'ChooseAlbumItemDialog' }
);
