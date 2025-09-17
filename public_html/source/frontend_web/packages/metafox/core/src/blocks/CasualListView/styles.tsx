import { makeStyles, createStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'flex',
        flexWrap: 'wrap'
      },
      gridList: {
        width: '100%',
        height: 'auto'
      },
      casualItem: {
        position: 'relative',
        flexGrow: 1
      },
      pinItem: {
        position: 'relative',
        flexBasis: 0,
        display: 'flex',
        flexDirection: 'column',
        minWidth: '200px',
        flexGrow: 1
      },
      image: {
        maxWidth: '100%',
        maxHeight: '100%',
        objectFit: 'cover',
        minWidth: '100%'
      },
      lastItem: {
        flexGrow: 20,
        flexBasis: '15%'
      }
    }),
  { name: 'CasualListView' }
);
