import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      item: {
        display: 'block'
      },
      itemOuter: {
        display: 'flex',
        borderRadius: theme.shape.borderRadius,
        border: theme.mixins.border('secondary'),
        overflow: 'hidden',
        padding: theme.spacing(2)
      },
      grid: {
        '& $itemOuter': {
          flexDirection: 'column',
          '$ $media': {
            width: '100%'
          }
        }
      },
      media: {
        width: 120
      },
      title: {
        '& a': {
          color: theme.palette.text.primary
        }
      },
      statistic: {
        display: 'flex',
        flexFlow: 'wrap',
        color: theme.palette.text.secondary
      },
      itemInner: {
        flex: 1,
        minWidth: 0,
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'center',
        marginLeft: theme.spacing(2)
      },
      wrapperInfoFlag: {
        display: 'block'
      }
    }),
  { name: 'MuiFeedEmbedUserList' }
);
