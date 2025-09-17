import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      itemOuter: {
        display: 'flex',
        borderRadius: theme.shape.borderRadius,
        border: theme.mixins.border('secondary'),
        backgroundColor: theme.mixins.backgroundColor('paper'),
        overflow: 'hidden'
      },
      default: {
        '& $itemOuter': {
          display: 'block'
        }
      },
      grid: {
        '& $itemOuter': {
          flexDirection: 'column'
        }
      },
      list: {
        '& $itemOuter': {
          flexDirection: 'row',
          [theme.breakpoints.down('sm')]: {
            flexDirection: 'column',
            '& .media': {
              width: '100% !important'
            }
          }
        }
      },
      profileLink: {
        fontWeight: theme.typography.fontWeightBold,
        color: `${theme.palette.text.primary} !important`
      }
    }),
  { name: 'MuiFeedEmbedCard' }
);
