import { useGlobal } from '@metafox/framework';
import { ButtonAction } from '@metafox/ui';
import {
  Box,
  Button,
  Checkbox,
  FormControlLabel,
  FormGroup,
  FormHelperText,
  Radio,
  RadioGroup,
  styled
} from '@mui/material';
import produce from 'immer';
import { camelCase } from 'lodash';
import React, { memo } from 'react';

const AnswerItem = styled(FormControlLabel, {
  name: 'AnswerItem'
})(({ theme }) => ({
  marginLeft: 0,
  marginBottom: theme.spacing(1),
  '&:last-child': {
    marginBottom: theme.spacing(0)
  }
}));

const RadioAnswer = styled(Radio, {
  name: 'RadioAnswer',
  shouldForwardProp: prop => prop !== 'isEmbedInFeed'
})<{ isEmbedInFeed?: boolean }>(({ theme, isEmbedInFeed }) => ({
  padding: theme.spacing(0, 1),
  ...(isEmbedInFeed && {
    paddingLeft: theme.spacing(0)
  })
}));

const CheckboxAnswer = styled(Checkbox, {
  name: 'CheckboxAnswer',
  shouldForwardProp: prop => prop !== 'isEmbedInFeed'
})<{ isEmbedInFeed?: boolean }>(({ theme, isEmbedInFeed }) => ({
  padding: theme.spacing(0, 1),
  ...(isEmbedInFeed && {
    paddingLeft: theme.spacing(0)
  })
}));

const BtnToggle = styled('span', {
  name: 'BtnToggle'
})(({ theme }) => ({
  color: theme.palette.primary.main,
  fontWeight: 'bold',
  '&:hover': {
    textDecoration: 'underline'
  }
}));

const ButtonWrapper = styled(Box, {
  name: 'ButtonWrapper'
})(({ theme }) => ({
  display: 'flex',
  marginTop: theme.spacing(2)
}));

const ButtonInner = styled(Box, {
  name: 'ButtonInner'
})(({ theme }) => ({
  fontWeight: 'bold',
  textTransform: 'capitalize'
}));

const CancelButton = styled(Box, {
  name: 'CancelButton'
})(({ theme }) => ({
  fontWeight: 'bold',
  marginLeft: theme.spacing(1)
}));

interface Props {
  voteAgain: any;
  pollId: any;
  identity: any;
  displayAnswers: any;
  isClosed: any;
  hideAnswers: any;
  isMultiple: any;
  answers: any;
  LIMIT_ANSWER_DISPLAY: any;
  isPending: any;
  canVote: any;
  canVoteAgain: any;
  isEmbedInFeed: any;
  setVoteAgain: any;
  setShowPoll: any;
  setIsCanViewVoteAnswer: any;
  setIsCanViewResult: any;
  canViewResultAfter: any;
}

const CheckBoxGroupForm = ({
  displayAnswers,
  disabled,
  handleCheckboxChange,
  viewMore,
  hideAnswers,
  isEmbedInFeed
}: any) => (
  <FormGroup>
    {displayAnswers?.length > 0
      ? displayAnswers.map((i, index) => (
          <AnswerItem
            key={i.id.toString()}
            control={
              <CheckboxAnswer
                disabled={disabled}
                color="primary"
                size="small"
                name={i.id.toString()}
                onChange={handleCheckboxChange}
                isEmbedInFeed={isEmbedInFeed}
                data-testid={camelCase(`checkbox_answer ${index + 1}`)}
              />
            }
            label={i.answer}
          />
        ))
      : null}
    {viewMore && hideAnswers?.length > 0
      ? hideAnswers.map((i, index) => (
          <AnswerItem
            key={i.id.toString()}
            control={
              <CheckboxAnswer
                disabled={disabled}
                color="primary"
                size="small"
                name={i.id.toString()}
                onChange={handleCheckboxChange}
                isEmbedInFeed={isEmbedInFeed}
                data-testid={camelCase(`checkbox_answer ${index + 1}`)}
              />
            }
            label={i.answer}
          />
        ))
      : null}
  </FormGroup>
);

const RadioGroupForm = ({
  value,
  handleRadioChange,
  displayAnswers,
  disabled,
  viewMore,
  hideAnswers,
  isEmbedInFeed
}: any) => (
  <RadioGroup value={value} onChange={handleRadioChange}>
    {displayAnswers?.length > 0 &&
      displayAnswers.map((i, index) => (
        <AnswerItem
          key={i.id.toString()}
          value={i.id.toString()}
          control={
            <RadioAnswer
              color="primary"
              size="small"
              disabled={disabled}
              isEmbedInFeed={isEmbedInFeed}
              data-testid={camelCase(`radio_answer ${index + 1}`)}
            />
          }
          label={i.answer}
        />
      ))}
    {viewMore && hideAnswers?.length > 0
      ? hideAnswers.map((i, index) => (
          <AnswerItem
            key={i.id.toString()}
            value={i.id.toString()}
            control={
              <RadioAnswer
                color="primary"
                size="small"
                disabled={disabled}
                isEmbedInFeed={isEmbedInFeed}
                data-testid={camelCase(`radio_answer ${index + 1}`)}
              />
            }
            label={i.answer}
          />
        ))
      : null}
  </RadioGroup>
);

function PollVoteForm({
  voteAgain,
  pollId,
  identity,
  displayAnswers,
  isClosed,
  hideAnswers,
  isMultiple,
  answers,
  LIMIT_ANSWER_DISPLAY,
  isPending,
  canVote,
  canVoteAgain,
  isEmbedInFeed,
  setVoteAgain,
  setShowPoll,
  setIsCanViewVoteAnswer,
  canViewResultAfter,
  setIsCanViewResult,
  disabled: forceDisabled
}: Props) {
  const { i18n, dispatch, useSession } = useGlobal();
  const { loggedIn } = useSession();

  const [viewMore, setViewMore] = React.useState(false);
  const [helperText, setHelperText] = React.useState<string>('');
  const [value, setValue] = React.useState<string>('');
  const [result, setResult] = React.useState<number[]>([]);

  const handleRadioChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const { value } = event.target;
    const answerId = parseInt(value);
    setValue(value);
    setResult([answerId]);
    setHelperText('');
  };

  const handleCheckboxChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const { checked, name } = event.target;
    const answerId = parseInt(name);

    if (checked) {
      setHelperText('');
      setResult(prev =>
        produce(prev, draft => {
          const index = draft.findIndex(item => item === answerId);

          if (index < 0) draft.push(answerId);
        })
      );
    } else {
      setResult(prev =>
        produce(prev, draft => {
          const index = draft.findIndex(item => item === answerId);

          if (index > -1) draft.splice(index, 1);
        })
      );
    }
  };

  const cancelVoteAgain = () => {
    setVoteAgain(false);
    setShowPoll(false);
  };

  const handleSubmit = onSuccess => {
    if (!result.length) {
      setHelperText(i18n.formatMessage({ id: 'please_select_an_option' }));
      onSuccess();

      return null;
    }

    dispatch({
      type: 'submitPoll',
      payload: {
        voteAgain,
        pollId,
        answers: result,
        identity
      },
      meta: {
        onSuccess: data => {
          setShowPoll(false);
          setIsCanViewVoteAnswer(data?.can_view_result_after_vote);
          setIsCanViewResult(data?.can_view_result);
          onSuccess();
        }
      }
    });
  };

  const disabled = forceDisabled || isClosed || (!canVote && !canVoteAgain);

  return (
    <form onSubmit={handleSubmit}>
      {!isMultiple ? (
        <RadioGroupForm
          value={value}
          handleRadioChange={handleRadioChange}
          displayAnswers={displayAnswers}
          disabled={disabled}
          viewMore={viewMore}
          hideAnswers={hideAnswers}
          isEmbedInFeed={isEmbedInFeed}
        />
      ) : (
        <CheckBoxGroupForm
          displayAnswers={displayAnswers}
          disabled={disabled}
          handleCheckboxChange={handleCheckboxChange}
          viewMore={viewMore}
          hideAnswers={hideAnswers}
          isEmbedInFeed={isEmbedInFeed}
        />
      )}
      {answers.length > LIMIT_ANSWER_DISPLAY ? (
        <Box mt={1.5}>
          <BtnToggle onClick={() => setViewMore(!viewMore)} role="button">
            {i18n.formatMessage({ id: viewMore ? 'view_less' : 'view_more' })}
          </BtnToggle>
        </Box>
      ) : null}
      {helperText ? <FormHelperText>{helperText}</FormHelperText> : null}
      {(canVote || canVoteAgain) && (
        <ButtonWrapper>
          {!isPending && loggedIn && (
            <ButtonInner>
              <ButtonAction
                action={handleSubmit}
                variant="outlined"
                disabled={disabled}
                size={isEmbedInFeed ? 'smaller' : 'medium'}
                color="primary"
                sx={{ fontWeight: 'bold' }}
                data-testid={camelCase('button_vote')}
              >
                {i18n.formatMessage({ id: 'vote' })}
              </ButtonAction>
            </ButtonInner>
          )}
          {voteAgain && (
            <CancelButton>
              <Button
                variant="outlined"
                disabled={disabled}
                size={isEmbedInFeed ? 'smaller' : 'medium'}
                color="primary"
                sx={{ fontWeight: 'bold' }}
                onClick={cancelVoteAgain}
                data-testid={camelCase('button_cancel')}

              >
                {i18n.formatMessage({ id: 'cancel' })}
              </Button>
            </CancelButton>
          )}
        </ButtonWrapper>
      )}
    </form>
  );
}

export default memo(PollVoteForm);
