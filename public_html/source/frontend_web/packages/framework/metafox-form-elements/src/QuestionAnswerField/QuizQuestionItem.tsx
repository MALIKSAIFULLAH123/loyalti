import { useGlobal } from '@metafox/framework';
import { Button, styled, TextField, Box } from '@mui/material';
import { camelCase, get, isString } from 'lodash';
import React, { memo } from 'react';
import ErrorMessage from '../ErrorMessage';
import AnswerItem from './AnswerItem';
import { QuizItemProps } from './types';

const name = 'QuizItem';
const StyledWrapperAnswer = styled('div', { name, slot: 'wrapper-answer' })(
  ({ theme }) => ({
    borderLeft: theme.mixins.border('secondary'),
    paddingLeft: theme.spacing(2),
    marginBottom: theme.spacing(1.5)
  })
);

const ButtonIcon = styled(Button)(({ theme }) => ({
  fontWeight: theme.typography.fontWeightBold,
  fontSize: theme.mixins.pxToRem(13),
  color: theme.palette.primary.main,
  cursor: 'pointer',
  '&:hover': {
    textDecoration: 'underline'
  }
}));

const WrapperError = styled(Box)(({ theme }) => ({
  margin: theme.spacing(1, 0)
}));

const QuizItem = ({
  index: questionIndex,
  item,
  name,
  disabled,
  maxLength,
  lastElement,
  error,
  submitCount,
  formik,
  handleChangeAnswer,
  handleChangeQuestion,
  addMoreQuestion,
  handleDeleteQuestion,
  addMoreAnswer,
  handleDeleteAnswer,
  handleChangeCorrectAnswer,
  handleTouched,
  maxAnswerLength
}: QuizItemProps) => {
  const { i18n } = useGlobal();
  const { question, answers } = item;
  const isSubmitted = React.useRef<boolean>(false);
  const [questionTouched, setQuestionTouched] = React.useState<boolean>(false);

  React.useEffect(() => {
    if (formik.isSubmitting) {
      isSubmitted.current = true;
    }
  }, [formik.isSubmitting]);

  const isErrorQuestion = Boolean(
    get(error, 'question') &&
      (questionTouched || (isSubmitted.current ? submitCount : 0))
  );
  // need check isString for show error BOTH answer else is error EACH answer
  const isErrorAnswerGeneral = Boolean(
    get(error, 'answers.[0]') &&
      isString(get(error, 'answers.[0]')) &&
      (questionTouched || (isSubmitted.current ? submitCount : 0))
  );

  const handleBlur = () => {
    handleTouched();
    setQuestionTouched(true);
  };

  return (
    <div>
      <TextField
        variant="outlined"
        margin="normal"
        fullWidth
        required
        label={i18n.formatMessage(
          { id: 'question_with_index' },
          { value: questionIndex + 1 }
        )}
        placeholder={i18n.formatMessage(
          { id: 'question_with_index' },
          { value: questionIndex + 1 }
        )}
        disabled={disabled}
        defaultValue={question}
        onChange={e => handleChangeQuestion(e, questionIndex)}
        error={isErrorQuestion}
        onBlur={handleBlur}
        inputProps={{
          maxLength,
          'data-testid': camelCase(`input question ${name}${questionIndex}`)
        }}
        data-testid={camelCase(`field question ${name}${questionIndex}`)}
      />
      {isErrorQuestion && <ErrorMessage error={get(error, 'question')} />}

      <StyledWrapperAnswer data-testid={camelCase(`box question ${name}`)}>
        <Box data-testid={camelCase(`questionAnswer ${questionIndex}`)}>
          {answers.length > 0 &&
            answers.map((item, index) => (
              <AnswerItem
                item={item}
                key={item.ordering}
                index={index}
                disabled={disabled}
                questionIndex={questionIndex}
                lastElement={index === answers.length - 1}
                addMoreAnswer={addMoreAnswer}
                handleDeleteAnswer={() => handleDeleteAnswer(item.ordering)}
                handleChangeAnswer={handleChangeAnswer}
                handleChangeCorrectAnswer={handleChangeCorrectAnswer}
                submitCount={isSubmitted.current ? submitCount : 0}
                error={get(error, `answers.[${index}].answer`)}
                handleTouched={handleTouched}
                maxLength={maxAnswerLength}
              />
            ))}
        </Box>
        <div>
          <ButtonIcon
            onClick={() => addMoreAnswer()}
            variant="text"
            color="primary"
            size="small"
            disabled={disabled}
            sx={{ fontWeight: 500 }}
            data-testid={camelCase(`button_add_answer ${name}`)}
          >
            {i18n.formatMessage({ id: 'add_answer' })}
          </ButtonIcon>
          <ButtonIcon
            onClick={handleDeleteQuestion}
            disabled={disabled}
            variant="text"
            size="small"
            color="primary"
            sx={{ fontWeight: 500 }}
            data-testid={camelCase(`button_remove_question ${name}`)}
          >
            {i18n.formatMessage({ id: 'remove_question' })}
          </ButtonIcon>
        </div>
        {isErrorAnswerGeneral ? (
          <WrapperError>
            <ErrorMessage error={get(error, 'answers')} />
          </WrapperError>
        ) : null}
      </StyledWrapperAnswer>
    </div>
  );
};

export default memo(QuizItem);
