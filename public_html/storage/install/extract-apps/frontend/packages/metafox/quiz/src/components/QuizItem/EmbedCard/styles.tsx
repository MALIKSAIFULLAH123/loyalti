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
      title: {
        '& a': {
          color: theme.palette.text.primary
        }
      },
      description: {
        color: theme.palette.text.secondary,
        '& p': {
          margin: 0
        }
      },
      subInfo: {},
      itemInner: {
        flex: 1,
        minWidth: 0,
        padding: theme.spacing(2),
        display: 'flex',
        flexDirection: 'column'
      },
      totalView: {
        color: theme.palette.text.secondary
      },
      price: {
        fontWeight: theme.typography.fontWeightBold,
        color: theme.palette.warning.main
      },
      wrapperInfoFlag: {
        marginTop: 'auto'
      },
      flagWrapper: {
        marginLeft: 'auto',
        display: 'inline-flex'
      }
    }),
  { name: 'MuiFeedEmbedProductTemplate' }
);
