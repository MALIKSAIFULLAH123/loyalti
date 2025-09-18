import {
  GlobalState,
  Link,
  useGetItem,
  useGetItems,
  useGlobal
} from '@metafox/framework';
import { EmbedQuizInFeedItemProps } from '@metafox/quiz';
import QuizQuestion from '@metafox/quiz/blocks/QuizDetail/QuizQuestion';
import { getQuizResultSelector } from '@metafox/quiz/selectors/quizResultSelector';
import {
  FeedEmbedCard,
  FeaturedFlag,
  SponsorFlag,
  Statistic,
  TruncateText,
  ButtonAction
} from '@metafox/ui';
import { Box, styled } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';
import HtmlViewer from '@metafox/html-viewer';

const FlagWrapper = styled('span', {
  name: 'QuizItem',
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

const ItemInner = styled('div', {
  name: 'QuizItem',
  slot: 'itemInner'
})(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  padding: theme.spacing(2),
  display: 'flex',
  flexDirection: 'column'
}));

const Title = styled(Box, {
  name: 'QuizItem',
  slot: 'title'
})(({ theme }) => ({
  marginBottom: theme.spacing(1),
  fontWeight: 600,
  '& a': {
    color: theme.palette.text.primary
  }
}));

const Description = styled(Box, {
  name: 'QuizItem',
  slot: 'description'
})(({ theme }) => ({
  marginBottom: theme.spacing(2),
  color: theme.palette.text.secondary,
  '& p': {
    margin: 0
  }
}));

const WrapperInfoFlag = styled(Box, {
  name: 'QuizItem',
  slot: 'wrapperInfoFlag'
})(({ theme }) => ({
  marginTop: 'auto',
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-end'
}));
const ButtonWrapper = styled(ButtonAction, { slot: 'ButtonWrapper' })(
  ({ theme }) => ({
    margin: theme.spacing(0, 0, 2),
    textTransform: 'capitalize'
  })
);
const Result = styled(Box, { slot: 'Result' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  lineHeight: 1.33,
  marginTop: theme.spacing(2),
  color: theme.palette.text.hint
}));

const Count = styled(Box, { slot: 'Count' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(13),
  fontWeight: theme.typography.fontWeightBold,
  textTransform: 'none',
  margin: theme.spacing(0)
}));

const PlayStyled = styled(Box, {
  name: 'QuizDetail',
  slot: 'btnPlay',
  shouldForwardProp: props => props !== 'isCanViewVoteAnswer'
})<{ isCanViewVoteAnswer?: boolean }>(({ theme, isCanViewVoteAnswer }) => ({
  display: 'inline-block',
  ...(isCanViewVoteAnswer && {
    fontSize: theme.mixins.pxToRem(13),
    color: theme.palette.primary.main,
    cursor: 'pointer',
    '&:hover': {
      textDecoration: 'underline'
    }
  })
}));
const StatisticStyled = styled(Statistic, {
  name: 'QuizDetail',
  slot: 'Statistic'
})(({ theme }) => ({
  '&:before': {
    color: theme.palette.text.secondary,
    content: '"·"',
    paddingLeft: '0.25em',
    paddingRight: '0.25em'
  }
}));

export default function EmbedQuizInFeedItemView({
  item,
  feed,
  isShared,
  questions
}: EmbedQuizInFeedItemProps) {
  const { dispatch, i18n, dialogBackend, useSession } = useGlobal();
  const { user: authUser } = useSession();

  const [resultSubmit, setQuizResult] = React.useState<Record<string, number>>(
    {}
  );

  const results = useSelector<GlobalState>(state =>
    getQuizResultSelector(state, item?.results)
  ) as Record<string, any>;

  const memberResults = useGetItems(item?.member_results);
  const userOwner = useGetItem(item?.user);

  if (!item) return null;

  const { statistic, id: quizId, is_pending, extra } = item;
  const canPlay = !results && !is_pending && extra?.can_play;

  const handleSetQuiz = value => {
    if (!authUser) {
      dispatch({
        type: 'user/showDialogLogin'
      });

      return;
    }

    setQuizResult(value);
  };

  const handleSubmit = handleEnableButton => {
    dispatch({
      type: 'quiz/submitQuiz',
      payload: {
        quiz_id: quizId,
        answers: resultSubmit
      },
      meta: { onSuccess: handleEnableButton, onError: handleEnableButton }
    });
  };

  const isCanViewVoteAnswer =
    Boolean(statistic?.total_play) &&
    (results ? true : item?.extra?.can_view_results_before_answer);

  const openDialogPlayed = () => {
    if (!isCanViewVoteAnswer) return;

    dialogBackend.present({
      component: 'quiz.dialog.PeoplePlayed',
      props: {
        dialogTitle: 'people_played_this',
        questions,
        user: memberResults,
        userOwner,
        item
      }
    });
  };

  return (
    <FeedEmbedCard
      bottomSpacing="normal"
      item={item}
      feed={feed}
      isShared={isShared}
    >
      <ItemInner data-testid="embedview">
        <Title>
          <Link to={item.link} identityTracking={feed?._identity}>
            <TruncateText variant="h4" lines={3}>
              {item.title}
            </TruncateText>
          </Link>
        </Title>
        <Description>
          <TruncateText variant={'body1'} lines={3}>
            <HtmlViewer html={item.description || ''} />
          </TruncateText>
        </Description>
        {questions?.length > 0 &&
          questions.map((i, index) => (
            <QuizQuestion
              key={index.toString()}
              question={i.question}
              questionId={i.id}
              answers={i.answers}
              order={index + 1}
              setQuizResult={handleSetQuiz}
              disabled={!canPlay}
              result={results?.user_result?.find(
                item => item.question_id === i.id
              )}
            />
          ))}
        {canPlay && (
          <Box>
            <ButtonWrapper
              disabled={isEmpty(resultSubmit)}
              variant="contained"
              color="primary"
              action={handleSubmit}
            >
              {i18n.formatMessage({ id: 'submit' })}
            </ButtonWrapper>
          </Box>
        )}
        <Result>
          {results && (
            <Box>
              {i18n.formatMessage(
                { id: 'you_have_correct_answer' },
                {
                  result: () => <strong>{results?.result_correct}</strong>
                }
              )}
            </Box>
          )}
        </Result>
        <WrapperInfoFlag>
          <Count>
            <PlayStyled
              isCanViewVoteAnswer={isCanViewVoteAnswer}
              onClick={openDialogPlayed}
            >
              {i18n.formatMessage(
                { id: 'total_play' },
                { value: statistic?.total_play || 0 }
              )}
            </PlayStyled>
            <StatisticStyled
              values={statistic}
              display={'total_view'}
              component={'span'}
              skipZero={false}
              color="text.secondary"
              variant="body2"
              fontWeight="fontWeightBold"
            />
          </Count>
          <FlagWrapper>
            <FeaturedFlag
              variant="text"
              value={item.is_featured}
              color="primary"
              showTitleMobile={false}
            />
            {item.is_sponsor ? (
              <SponsorFlag
                variant="text"
                value={item.is_sponsor}
                color="yellow"
                showTitleMobile={false}
                item={item}
              />
            ) : null}
          </FlagWrapper>
        </WrapperInfoFlag>
      </ItemInner>
    </FeedEmbedCard>
  );
}
