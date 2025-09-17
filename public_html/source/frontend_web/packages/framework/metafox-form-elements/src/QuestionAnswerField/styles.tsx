import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      question: {
        marginBottom: theme.spacing(1)
      },
      title: {
        fontWeight: theme.typography.fontWeightBold,
        fontSize: theme.mixins.pxToRem(15)
      },
      titleWrapper: {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center'
      },
      answerItem: {
        display: 'flex',
        alignItems: 'center'
      },
      endAdornmentButton: {
        width: 'calc(100% / 3 * 1)',
        display: 'flex',
        alignItems: 'center'
      },
      buttonWrapper: {
        display: 'flex',
        width: '100%',
        justifyContent: 'flex-start',
        alignItem: 'center'
      },

      radioButton: {
        width: 40,
        height: 40
      },
      input: {
        padding: theme.spacing(0, 2),
        height: 48,
        '&::placeholder': {
          color: theme.palette.text.secondary,
          opacity: 1
        }
      },
      button: {
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
      questionInput: {
        '& .MuiOutlinedInput-root': {
          borderRadius: theme.spacing(0.5)
        }
      },
      answerInput: {
        width: 'calc(100% / 3 * 2)',
        '& .MuiOutlinedInput-root': {
          borderRadius: theme.spacing(0.5)
        }
      },
      endAdornment: {
        cursor: 'pointer'
      }
    }),
  {
    name: 'QuizQuestion'
  }
);
