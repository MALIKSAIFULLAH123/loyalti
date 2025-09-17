import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      answerInput: {
        width: 'calc(100% - 130px)'
      },
      answerItem: {
        display: 'flex'
      },
      input: {
        '&::placeholder': {
          color: theme.palette.text.secondary,
          opacity: 1
        }
      },
      endAdornment: {
        cursor: 'pointer'
      },
      endAdornmentButton: {
        display: 'flex',
        alignItems: 'center',
        paddingLeft: theme.spacing(2)
      },
      buttonWrapper: {
        display: 'flex',
        width: '100%',
        justifyContent: 'space-between',
        marginRight: theme.spacing(1),
        marginTop: theme.spacing(1),
        '& button': {
          minWidth: theme.spacing(5)
        }
      },
      button: {
        '& + button': {
          marginLeft: theme.spacing(1)
        },
        fontWeight: theme.typography.fontWeightBold,
        fontSize: theme.mixins.pxToRem(13),
        color: theme.palette.primary.main,
        cursor: 'pointer',
        display: 'flex',
        alignItems: 'center',
        '&:hover': {
          textDecoration: 'underline'
        }
      },
      visible: {
        visibility: 'hidden'
      }
    }),
  {
    name: 'AnswerPollItem'
  }
);
