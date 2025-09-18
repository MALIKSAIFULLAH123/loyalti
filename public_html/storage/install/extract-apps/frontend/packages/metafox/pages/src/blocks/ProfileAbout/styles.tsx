import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      textInfo: {
        fontSize: theme.mixins.pxToRem(15),
        color: theme.palette.text.primary,
        marginBottom: theme.spacing(1.5),
        '& p': {
          wordBreak: 'break-word'
        }
      },
      item: {
        ...theme.typography.body1,
        color: theme.palette.text.secondary,
        overflow: 'hidden',
        '&:not(:last-child)': {
          marginBottom: theme.spacing(1.5)
        }
      },
      icon: {
        fontSize: theme.typography.fontSize,
        float: 'left',
        marginRight: theme.spacing(1),
        lineHeight: '18px'
      },
      text: {
        display: 'block',
        overflow: 'hidden'
      }
    }),
  { name: 'MuiPageProfileAbout' }
);
