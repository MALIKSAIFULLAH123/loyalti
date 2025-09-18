import { useGlobal, useSession } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, LinearProgress, Typography, styled } from '@mui/material';
import { camelCase } from 'lodash';
import React, { memo } from 'react';

const ProgressAnswer = styled(Box, {
  name: 'ProgressAnswer'
})(({ theme }) => ({
  color: theme.palette.text.hint,
  marginBottom: theme.spacing(1),
  '&:last-child': {
    marginBottom: theme.spacing(0)
  }
}));

const ProgressItem = styled(Box, {
  name: 'ProgressItem'
})(({ theme }) => ({
  height: theme.spacing(2.5),
  display: 'flex',
  alignItems: 'center'
}));

const Progress = styled(LinearProgress, {
  name: 'Progress'
})(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  margin: 0,
  marginRight: theme.spacing(1),
  height: `${theme.spacing(1)} !important`,
  borderRadius: theme.shape.borderRadius / 2,
  '& > div': {
    borderRadius: theme.shape.borderRadius / 2
  }
}));

const ProgressPercent = styled(Typography, {
  name: 'ProgressPercent'
})(({ theme }) => ({
  width: 20,
  marginLeft: theme.spacing(1),
  color: theme.palette.text.secondary,
  fontSize: theme.mixins.pxToRem(13),
  wordWrap: 'normal'
}));

const NoShowAnswer = styled(Box, {
  name: 'NoShowAnswer'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  paddingBottom: theme.spacing(2),
  '&:last-child': {
    paddingBottom: 0
  }
}));

const IconNoShowAnswer = styled(LineIcon, {
  name: 'IconNoShowAnswer'
})(({ theme }) => ({
  padding: theme.spacing(1, 1, 1, 0),
  fontSize: theme.mixins.pxToRem(16)
}));

const ProgressAnswerWrapper = styled(Box, {
  name: 'ProgressAnswerWrapper'
})(({ theme }) => ({
  paddingRight: theme.spacing(3),
  '&:last-child': {
    marginBottom: theme.spacing(0)
  }
}));

const BtnToggle = styled('span', {
  name: 'BtnToggle'
})(({ theme }) => ({
  color: theme.palette.text.primary,
  fontWeight: 'bold',
  '&:hover': {
    textDecoration: 'underline'
  }
}));

const Title = styled(Typography, {
  name: 'Title',
  shouldForwardProp: prop => prop !== 'styleGroup'
})<{ styleGroup?: string }>(({ theme, styleGroup }) => ({
  ...(styleGroup === 'votedAnswer' && {
    fontWeight: 'bold',
    color: theme.palette.text.primary
  }),
  ...(styleGroup === 'answerLabel' && {})
}));
interface Props {
  displayAnswers: any;
  answers: any;
  publicVote: any;
  LIMIT_ANSWER_DISPLAY: any;
  hideAnswers: any;
  isMultiple: any;
  isCanViewVoteAnswer: any;
  isCanViewResult: any;
}
type ItemProps = {
  item: any;
  settings?: any;
};

const PollVoteAnswerItem = ({ item, settings }: ItemProps) => {

  return (
    <ProgressAnswer>
      <Title
        variant={item.voted ? 'h5' : 'body1'}
        styleGroup={item.voted ? 'votedAnswer' : 'answerLabel'}
      >
        {item.answer}
      </Title>
      <ProgressItem data-testid={camelCase(`process_bar ${item.id}`)}>
        <Progress variant="determinate" value={item.vote_percentage || 0} />
        {settings?.publicVote && (
          <ProgressPercent component="span">
            {`${item.vote_percentage}%`}
          </ProgressPercent>
        )}
      </ProgressItem>
    </ProgressAnswer>
  );
};

const PollVoteNoShowAnswer = ({ item, settings }: ItemProps) => {
  const { user: authUser } = useSession();

  const checkIsOwner = () => {
    if (item?.some_votes) {
      return !!item?.some_votes?.map(user => user.id === authUser?.id);
    }

    return false;
  };
  let icon = settings?.isMultiple ? 'ico-square-o' : ' ico-circle-o';

  if (settings?.isMultiple && item.voted && checkIsOwner()) {
    icon = 'ico-check-square';
  }

  if (!settings?.isMultiple && item.voted && checkIsOwner()) {
    icon = 'ico-check-circle';
  }

  return (
    <NoShowAnswer>
      <IconNoShowAnswer icon={icon} />
      <Title
        variant={item.voted ? 'h5' : 'body1'}
        styleGroup={item.voted ? 'votedAnswer' : 'answerLabel'}
      >
        {item.answer}
      </Title>
    </NoShowAnswer>
  );
};

function PollVoteAnswer({
  displayAnswers,
  answers,
  publicVote,
  LIMIT_ANSWER_DISPLAY,
  hideAnswers,
  isMultiple,
  isCanViewVoteAnswer,
  isCanViewResult
}: Props) {
  const { i18n } = useGlobal();
  const [viewMore, setViewMore] = React.useState(false);

  return (
    <ProgressAnswerWrapper>
      {displayAnswers?.length > 0 &&
        displayAnswers.map((item, index) => {
          return isCanViewVoteAnswer && isCanViewResult ? (
            <PollVoteAnswerItem
              key={index}
              item={item}
              settings={{ publicVote }}
            />
          ) : (
            <PollVoteNoShowAnswer
              key={index}
              item={item}
              settings={{ publicVote, isMultiple }}
            />
          );
        })}
      {viewMore && hideAnswers?.length > 0
        ? hideAnswers.map((item, index) => {
            return isCanViewVoteAnswer && isCanViewResult ? (
              <PollVoteAnswerItem
                key={index}
                item={item}
                settings={{ publicVote }}
              />
            ) : (
              <PollVoteNoShowAnswer
                key={index}
                item={item}
                settings={{ publicVote, isMultiple }}
              />
            );
          })
        : null}
      {answers.length > LIMIT_ANSWER_DISPLAY ? (
        <BtnToggle onClick={() => setViewMore(!viewMore)} role="button">
          {i18n.formatMessage({ id: viewMore ? 'view_less' : 'view_more' })}
        </BtnToggle>
      ) : null}
    </ProgressAnswerWrapper>
  );
}

export default memo(PollVoteAnswer);
