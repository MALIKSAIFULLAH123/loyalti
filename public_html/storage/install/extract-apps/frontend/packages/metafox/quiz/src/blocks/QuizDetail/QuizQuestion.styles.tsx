import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      right: {},
      wrong: {},
      option: {
        '& .Mui-disabled': {
          color: theme.palette.text.secondary
        }
      },
      quizWrapper: {
        marginTop: theme.spacing(2),
        marginBottom: theme.spacing(2),
        borderRadius: theme.shape.borderRadius,
        border: theme.mixins.border('secondary'),
        padding: theme.spacing(2),
        '&$right': {
          border: '1px solid',
          borderColor: theme.palette.success.main,
          '& $option .Mui-checked + span': {
            color: theme.palette.success.main,
            fontWeight: 'bold'
          }
        },
        '&$wrong': {
          border: '1px solid',
          borderColor: theme.palette.error.main,
          '& $option .Mui-checked + span': {
            color: theme.palette.error.main,
            fontWeight: 'bold'
          }
        }
      },
      question: {
        fontSize: theme.mixins.pxToRem(15),
        lineHeight: 1.33,
        fontWeight: 'bold',
        color: theme.palette.text.primary,
        marginBottom: theme.spacing(1)
      },
      formControl: {}
    }),
  { name: 'QuizQuestion' }
);

export default useStyles;
