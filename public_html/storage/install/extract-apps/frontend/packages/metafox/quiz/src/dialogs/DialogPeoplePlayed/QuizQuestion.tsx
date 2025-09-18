import { GlobalState } from '@metafox/framework';
import { getQuizAnswersSelector } from '@metafox/quiz/selectors/quizAnswersSelector';
import { CheckCircleOutline, RadioButtonUnchecked } from '@mui/icons-material';
import {
  Box,
  FormControlLabel,
  FormLabel,
  Radio,
  RadioGroup,
  styled
} from '@mui/material';
import { isArray } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';

const name = 'quiz-question-dialog';

const RootStyled = styled(Box, {
  name,
  slot: 'root',
  shouldForwardProp: props => props !== 'rightAnswer'
})<{ rightAnswer?: boolean }>(({ theme, rightAnswer }) => ({
  marginTop: theme.spacing(3),
  marginBottom: theme.spacing(1),
  ...(rightAnswer
    ? {
        '& .option .Mui-checked + span': {
          color: theme.palette.success.main,
          fontWeight: 'bold'
        }
      }
    : {
        '& .option .Mui-checked + span': {
          color: theme.palette.error.main,
          fontWeight: 'bold'
        }
      })
}));

const RadioButton = styled(Radio, { slot: 'RadioButton' })(({ theme }) => ({
  '&.Mui-disabled': {
    ...(theme.palette.mode === 'dark' && {
      color: theme.palette.text.hint
    })
  }
}));

const QuestionFormLabel = styled(FormLabel, { slot: 'label' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  lineHeight: 1.33,
  fontWeight: 'bold',
  color: theme.palette.text.primary,
  marginBottom: theme.spacing(1)
}));

export default function QuizQuestion(props) {
  const { question, answers, order, result } = props;

  const quizAnswers = useSelector<GlobalState>(state =>
    getQuizAnswersSelector(state, answers)
  );

  const rightAnswer = Boolean(
    result?.correct_answer_id === result?.user_answer_id
  );

  return (
    <RootStyled rightAnswer={rightAnswer}>
      <QuestionFormLabel variant="h5" component="div">
        {`${order}. ${question}`}
      </QuestionFormLabel>
      <RadioGroup value={result?.user_answer_id.toString()}>
        {isArray(quizAnswers)
          ? quizAnswers.map(i => (
              <FormControlLabel
                disabled
                key={i.id.toString()}
                value={i.id.toString()}
                style={{ width: 'fit-content' }}
                control={
                  <RadioButton
                    color="primary"
                    size="small"
                    icon={
                      i.id === result?.correct_answer_id ? (
                        <CheckCircleOutline fontSize="small" />
                      ) : (
                        <RadioButtonUnchecked fontSize="small" />
                      )
                    }
                  />
                }
                label={i.answer}
                className="option"
              />
            ))
          : null}
      </RadioGroup>
    </RootStyled>
  );
}
