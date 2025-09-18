import { useGlobal } from '@metafox/framework';
import { FeaturedFlag, SponsorFlag, Statistic } from '@metafox/ui';
import { Box, Button, styled, Tooltip } from '@mui/material';
import React from 'react';
import PollVoteAnswer from './PollVoteAnswer';
import PollVoteForm from './PollVoteForm';
import moment from 'moment';
import { camelCase } from 'lodash';

const FlagWrapper = styled('span', {
  name: 'PollVoteFormRoot',
  slot: 'flagWrapper'
})(({ theme }) => ({
  marginLeft: 'auto',
  '& > .MuiFlag-root': {
    marginLeft: theme.spacing(2.5),
    [theme.breakpoints.down('sm')]: {
      marginLeft: theme.spacing(0.5)
    }
  }
}));

const ClosedStyled = styled(Box, {
  name: 'ClosedStyled'
})(({ theme }) => ({
  '&:before': {
    color: theme.palette.text.secondary,
    content: '"·"',
    paddingLeft: '0.25em',
    paddingRight: '0.25em'
  }
}));

const Root = styled(Box, {
  name: 'Root',
  shouldForwardProp: prop => prop !== 'isEmbedInFeed'
})<{ isEmbedInFeed?: boolean }>(({ theme, isEmbedInFeed }) => ({
  ...(!isEmbedInFeed && {
    border: theme.mixins.border('secondary'),
    borderRadius: theme.shape.borderRadius,
    padding: theme.spacing(2)
  }),
  ...(isEmbedInFeed && {
    paddingTop: theme.spacing(1)
  })
}));

const ButtonWrapper = styled(Box, {
  name: 'ButtonWrapper'
})(({ theme }) => ({
  display: 'flex',
  marginTop: theme.spacing(2)
}));

const ButtonInner = styled(Button, {
  name: 'ButtonInner'
})(({ theme }) => ({
  fontWeight: 'bold',
  textTransform: 'capitalize'
}));

const VoteStatistic = styled(Box, {
  name: 'VoteStatistic'
})(({ theme }) => ({
  marginTop: theme.spacing(2),
  color: theme.palette.text.hint,
  fontSize: theme.mixins.pxToRem(13),
  display: 'flex'
}));

const ActiveTotalVote = styled(Box, {
  name: 'ActiveTotalVote'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(13),
  color: theme.palette.primary.main,
  cursor: 'pointer',
  '&:hover': {
    textDecoration: 'underline'
  }
}));

const TotalVote = styled(Statistic, {
  name: 'TotalVote'
})(({ theme }) => ({
  '&:hover': {
    textDecoration: 'underline'
  }
}));

const TimeLeft = styled(Tooltip, {
  name: 'TimeLeft'
})(({ theme }) => ({
  marginLeft: theme.spacing(2)
}));

export type PollVoteFormProps = {
  answers: Record<string, any>[];
  statistic: Record<string, number>;
  closeTime: string;
  pollId: number;
  isVoted: boolean;
  isMultiple?: boolean;
  publicVote?: boolean;
  identity: string;
  isPending?: boolean;
  canVoteAgain?: boolean;
  canVote?: boolean;
  canViewResult?: boolean;
  canViewResultAfter?: boolean;
  canViewResultBefore?: boolean;
  isEmbedInFeed?: boolean;
  isFeatured?: boolean;
  isSponsor?: boolean;
  isClosed?: boolean;
  disabled?: boolean;
};

export default function PollVoteFormRoot(props: PollVoteFormProps) {
  const {
    answers: notSortAnswer,
    statistic,
    closeTime,
    pollId,
    isVoted,
    isMultiple,
    publicVote,
    identity,
    isPending,
    canVoteAgain,
    canVote,
    canViewResult,
    canViewResultAfter,
    canViewResultBefore,
    isEmbedInFeed,
    isFeatured,
    isSponsor,
    isClosed,
    disabled = false
  } = props;
  const answers = notSortAnswer.sort((a, b) => a?.ordering - b?.ordering);
  const { i18n, dialogBackend } = useGlobal();
  const [showPoll, setShowPoll] = React.useState<boolean>(true);
  const [voteAgain, setVoteAgain] = React.useState<boolean>(false);
  const [isCanViewVoteAnswer, setIsCanViewVoteAnswer] =
    React.useState<boolean>(false);

  const [isCanViewResult, setIsCanViewResult] =
    React.useState<boolean>(canViewResult);

  const LIMIT_ANSWER_DISPLAY = isEmbedInFeed ? 3 : answers.length;

  const displayAnswers = answers.slice(0, LIMIT_ANSWER_DISPLAY);
  const hideAnswers = answers.slice(LIMIT_ANSWER_DISPLAY, answers.length);

  React.useEffect(() => {
    if (isCanViewResult) {
      if (isVoted) {
        setIsCanViewVoteAnswer(canViewResultAfter);
      } else {
        setIsCanViewVoteAnswer(canViewResultBefore);
      }
    }
  }, [
    isCanViewResult,
    canViewResult,
    isVoted,
    canViewResultAfter,
    canViewResultBefore
  ]);

  React.useEffect(() => {
    if (statistic.total_vote === 0) {
      setIsCanViewVoteAnswer(false);
    }
  }, [statistic]);

  React.useEffect(() => {
    setShowPoll(Boolean(!isVoted));
  }, [isVoted]);

  const handleVoteAgain = () => {
    setVoteAgain(true);
    setShowPoll(true);
  };

  return (
    <Root isEmbedInFeed={isEmbedInFeed}>
      {showPoll ? (
        <PollVoteForm
          voteAgain={voteAgain}
          pollId={pollId}
          identity={identity}
          displayAnswers={displayAnswers}
          isClosed={isClosed}
          hideAnswers={hideAnswers}
          isMultiple={isMultiple}
          answers={answers}
          LIMIT_ANSWER_DISPLAY={LIMIT_ANSWER_DISPLAY}
          isPending={isPending}
          canVote={canVote}
          canVoteAgain={canVoteAgain}
          isEmbedInFeed={isEmbedInFeed}
          setShowPoll={setShowPoll}
          setVoteAgain={setVoteAgain}
          setIsCanViewVoteAnswer={setIsCanViewVoteAnswer}
          setIsCanViewResult={setIsCanViewResult}
          canViewResultAfter={canViewResultAfter}
          disabled={disabled}
        />
      ) : (
        <PollVoteAnswer
          displayAnswers={displayAnswers}
          answers={answers}
          publicVote={publicVote}
          LIMIT_ANSWER_DISPLAY={LIMIT_ANSWER_DISPLAY}
          hideAnswers={hideAnswers}
          isMultiple={isMultiple}
          isCanViewVoteAnswer={isCanViewVoteAnswer}
          isCanViewResult={isCanViewResult}
        />
      )}
      {!showPoll && canVoteAgain && (
        <ButtonWrapper>
          <ButtonInner
            variant="outlined"
            size={isEmbedInFeed ? 'smaller' : 'medium'}
            color="primary"
            onClick={handleVoteAgain}
            sx={{ fontWeight: 'bold' }}
            disabled={disabled}
            data-testid={camelCase('button_vote_again')}
          >
            {i18n.formatMessage({ id: 'vote_again' })}
          </ButtonInner>
        </ButtonWrapper>
      )}
      <VoteStatistic>
        {isCanViewVoteAnswer && isCanViewResult && !disabled ? (
          <ActiveTotalVote
            data-testid={camelCase('button_total')}
            onClick={() =>
              dialogBackend.present({
                component: 'poll.dialog.PeopleWhoVotedAnswer',
                props: {
                  listAnswers: answers
                }
              })
            }
          >
            {i18n.formatMessage(
              {
                id: 'total_vote'
              },
              { value: statistic.total_vote }
            )}
          </ActiveTotalVote>
        ) : (
          <TotalVote
            color="textHint"
            values={statistic}
            display={'total_vote'}
            skipZero={false}
            data-testid={camelCase('button_total')}
          />
        )}
        {closeTime && !isClosed ? (
          <TimeLeft title={moment(closeTime).format('llll')}> 
            <span data-testid={camelCase('span_TimeLeft')}>
              {i18n.formatMessage(
                { id: 'time_left' },
                { time: moment(closeTime).fromNow(true) }
              )}
            </span>
          </TimeLeft>
        ) : null}
        {isClosed ? (
          <ClosedStyled color={'text.hint'}>
            {i18n.formatMessage({ id: 'voting_closed' })}
          </ClosedStyled>
        ) : null}
        {isEmbedInFeed ? (
          <FlagWrapper>
            <FeaturedFlag
              variant="text"
              value={isFeatured}
              color="primary"
              showTitleMobile={false}
            />
            <SponsorFlag
              variant="text"
              value={isSponsor}
              color="yellow"
              showTitleMobile={false}
            />
          </FlagWrapper>
        ) : null}
      </VoteStatistic>
    </Root>
  );
}
