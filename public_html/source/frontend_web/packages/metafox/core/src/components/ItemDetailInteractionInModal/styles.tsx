import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      profileLink: {
        fontWeight: theme.typography.fontWeightBold
      },
      statusRoot: {
        display: 'block',
        marginBottom: theme.spacing(2),
        '& .profileLink, & .profileLink:active': {
          color: theme.palette.text.primary
        },
        '& a': {
          color: theme.palette.text.primary,
          '&:hover': {
            textDecoration: 'underline'
          }
        }
      },
      headerHeadline: {
        fontSize: theme.mixins.pxToRem(15),
        '& a:not(.simpleLink)': {
          fontWeight: theme.typography.fontWeightBold,
          color: theme.palette.text.primary
        },
        '& a:hover': {
          textDecoration: 'underline'
        }
      }
    }),
  { name: 'ItemDetailInteractionInModal' }
);
