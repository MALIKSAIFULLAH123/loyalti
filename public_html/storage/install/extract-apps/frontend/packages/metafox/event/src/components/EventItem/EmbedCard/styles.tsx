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
        backgroundColor: theme.mixins.backgroundColor('paper'),
        overflow: 'hidden'
      },
      grid: {
        '& $itemOuter': {
          flexDirection: 'column'
        },
        '& $media': {
          width: '100% !important',
          height: '200px !important'
        }
      },
      list: {
        '& $itemOuter': {
          flexDirection: 'row',
          '& $media': {
            width: '200px'
          }
        },
        '& $wrapperInfoFlag': {
          marginTop: 'auto'
        }
      },
      media: {
        backgroundRepeat: 'no-repeat',
        backgroundPosition: 'center',
        backgroundSize: 'cover'
      },
      title: {
        '& a': {
          color: theme.palette.text.primary
        }
      },
      description: {
        color: theme.palette.text.hint,
        '& p': {
          margin: 0
        }
      },
      hostLink: {
        color: theme.palette.text.secondary
      },
      subInfo: {},
      itemInner: {
        flex: 1,
        minWidth: 0,
        padding: theme.spacing(2),
        display: 'flex',
        flexDirection: 'column'
      },
      price: {
        fontWeight: theme.typography.fontWeightBold,
        color: theme.palette.warning.main
      },
      flagWrapper: {
        marginLeft: 'auto'
      },
      highlightSubInfo: {},
      actions: {
        marginRight: theme.spacing(1.5),
        display: 'flex'
      },
      wrapperInfoFlag: {}
    }),
  { name: 'MuiFeedEventTemplate' }
);
