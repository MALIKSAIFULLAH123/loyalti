import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      clearFix: {
        '&:after': {
          clear: 'both',
          content: "'.'",
          display: 'block',
          fontSize: '0',
          height: '0',
          lineHeight: 0,
          visibility: 'hidden'
        }
      },
      dockContainer: {
        position: 'fixed',
        right: '0',
        margin: '0 60px 0 0',
        bottom: '0',
        zIndex: 98,
        height: '30px',
        transform: 'translateZ(0)'
      },
      nubContainer: {},
      panelContainer: {
        position: 'relative',
        display: 'flex',
        flexDirection: 'row-reverse'
      }
    }),
  { name: 'ChatplusSiteDock' }
);
