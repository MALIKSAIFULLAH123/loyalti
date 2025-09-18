import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'flex',
        padding: '0',
        margin: '0',
        listStyle: 'none'
      },
      item: {
        position: 'relative',
        lineHeight: '34px',
        padding: '0',
        margin: '0',
        '.panelHeader &': {
          height: '30px',
          width: '30px',
          marginLeft: theme.spacing(0.5)
        },
        '.panelHeader &:hover button': {
          color: theme.palette.text.secondary,
          backgroundColor: theme.palette.action.selected,
          borderRadius: '50%'
        }
      },
      btn: {
        height: '34px',
        lineHeight: '20px',
        fontSize: '17px',
        padding: '0 6px',
        cursor: 'pointer',
        color:
          theme.palette.mode === 'light'
            ? theme.palette.grey['600']
            : theme.palette.text.secondary,
        position: 'relative',
        minWidth: theme.spacing(3),
        display: 'inline-flex',
        textAlign: 'center',
        alignItems: 'center',
        justifyContent: 'center',
        border: 'none',
        outline: 'none',
        backgroundColor: 'transparent',
        '.panelHeader &': {
          height: '30px',
          width: '30px'
        }
      },
      itemActive: {},
      btnActive: {
        color: theme.palette.primary.main
      }
    }),
  { name: 'ChatDockToolbar' }
);
