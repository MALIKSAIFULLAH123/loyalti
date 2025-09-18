import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        position: 'relative',
        padding: theme.spacing(2),
        alignItems: 'center',
        display: 'flex',
        flexDirection: 'row'
      },
      media: {
        overflow: 'hidden',
        position: 'relative'
      },
      text: {
        flex: 1,
        overflow: 'hidden',
        paddingLeft: theme.spacing(2),
        paddingRight: theme.spacing(2),
        flexBasis: 'auto'
      },
      title: {
        color: theme.palette.text.primary,
        fontSize: theme.mixins.pxToRem(18),
        marginBottom: '0.1em',
        transition: 'all 300ms ease',
        whiteSpace: 'nowrap',
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        fontWeight: theme.typography.fontWeightBold
      },
      summary: {},
      stats: {
        fontSize: '0.8125rem',
        color: theme.palette.text.secondary,
        display: 'inline-block'
      }
    }),
  { name: 'UserItemSmallCard' }
);
