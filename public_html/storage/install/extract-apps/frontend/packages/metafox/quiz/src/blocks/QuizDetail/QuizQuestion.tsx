import { GlobalState } from '@metafox/framework';
import { getQuizAnswersSelector } from '@metafox/quiz/selectors/quizAnswersSelector';
import { CheckCircleOutline, RadioButtonUnchecked } from '@mui/icons-material';
import {
  FormControlLabel,
  FormHelperText,
  FormLabel,
  Radio,
  RadioGroup,
  styled
} from '@mui/material';
import clsx from 'clsx';
import produce from 'immer';
import { camelCase, isArray } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';
import useStyles from './QuizQuestion.styles';

const RadioButton = styled(Radio, { slot: 'RadioButton' })(({ theme }) => ({
  '&.Mui-disabled': {
    ...(theme.palette.mode === 'dark' && {
      color: theme.palette.text.hint
    })
  }
}));

export default function QuizQuestion(props) {
  const {
    question,
    answers,
    order,
    result,
    questionId,
    setQuizResult,
    disabled = false
  } = props;

  const quizAnswers = useSelector<GlobalState>(state =>
    getQuizAnswersSelector(state, answers)
  );

  const isSubmitted = Boolean(result);

  const rightAnswer = isSubmitted
    ? Boolean(result?.correct_answer_id === result?.user_answer_id)
    : false;
  const classes = useStyles();
  const [value, setValue] = React.useState('');

  const [helperText, setHelperText] = React.useState('');

  const handleRadioChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const { value } = event.target;
    const answer_id = parseInt(value);
    setValue(value);
    setQuizResult(prev =>
      produce(prev, draft => {
        draft[questionId] = answer_id;
      })
    );
    setHelperText('');
  };

  return (
    <div
      className={clsx(
        classes.quizWrapper,
        isSubmitted && (rightAnswer ? classes.right : classes.wrong)
      )}
      data-testid={camelCase(`quizWrapper ${order}`)}
    >
      <FormLabel variant="h5" component="div" className={classes.question}>
        {`${order}. ${question}`}
      </FormLabel>
      <RadioGroup
        value={isSubmitted ? result?.user_answer_id.toString() : value}
        onChange={handleRadioChange}
      >
        {isArray(quizAnswers)
          ? quizAnswers.map((i, index) => (
              <FormControlLabel
                disabled={Boolean(result || disabled)}
                key={i.id.toString()}
                value={i.id.toString()}
                style={{ width: 'fit-content' }}
                control={
                  <RadioButton
                    color="primary"
                    size="small"
                    data-testid={(`radioButton${order}.${index + 1}`)}
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
                className={classes.option}
              />
            ))
          : null}
      </RadioGroup>
      {helperText ? <FormHelperText>{helperText}</FormHelperText> : null}
    </div>
  );
}
