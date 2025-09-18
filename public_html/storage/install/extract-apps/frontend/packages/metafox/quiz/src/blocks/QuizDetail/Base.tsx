import { GlobalState, useGlobal } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { Block, BlockContent } from '@metafox/layout';
import { getQuizResultSelector } from '@metafox/quiz/selectors/quizResultSelector';
import {
  AttachmentItem,
  FeaturedFlag,
  ItemTitle,
  SponsorFlag,
  Statistic,
  RichTextViewMore,
  HtmlViewerWrapper,
  AuthorInfo,
  ButtonAction
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, Typography, styled } from '@mui/material';
import React from 'react';
import { useSelector } from 'react-redux';
import QuizQuestion from './QuizQuestion';
import { camelCase, isEmpty } from 'lodash';

const ContentWrapper = styled('div', { name: 'ContentWrapper' })(
  ({ theme }) => ({
    position: 'relative'
  })
);

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

const BgCover = styled('div', {
  name: 'QuizDetail',
  slot: 'bgCover'
})(({ theme }) => ({
  position: 'relative',
  zIndex: 2,
  backgroundRepeat: 'no-repeat',
  backgroundPosition: 'center',
  backgroundSize: 'contain',
  height: 320
}));

const BgCoverBlur = styled('div', {
  name: 'QuizDetail',
  slot: 'bgCover',
  shouldForwardProp: prop => prop !== 'isModalView'
})(({ theme }) => ({
  position: 'absolute',
  left: 0,
  right: 0,
  bottom: 0,
  top: 0,
  backgroundRepeat: 'no-repeat',
  backgroundPosition: 'center',
  backgroundSize: 'cover',
  zIndex: 1,
  backgroundColor: theme.palette.background.default,
  filter: 'blur(100px)'
}));

const ViewContainer = styled(Box, { slot: 'ViewContainer' })(({ theme }) => ({
  width: '100%',
  marginLeft: 'auto',
  marginRight: 'auto',
  padding: theme.spacing(2),
  position: 'relative'
}));

const ItemContent = styled(Box, { slot: 'ItemContent' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  lineHeight: 1.33,
  '& p + p': {
    marginBottom: theme.spacing(2.5)
  }
}));

const ActionMenu = styled(Box, { slot: 'ActionMenu' })(({ theme }) => ({
  width: 32,
  height: 32,
  [theme.breakpoints.down('sm')]: {
    position: 'absolute',
    top: theme.spacing(-1),
    right: theme.spacing(-1)
  },
  '& .ico': {
    color: theme.palette.text.secondary,
    fontSize: theme.mixins.pxToRem(13)
  }
}));

const AttachmentTitle = styled(Box, { slot: 'AttachmentTitle' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(18),
    marginTop: theme.spacing(4),
    color: theme.palette.text.secondary,
    fontWeight: theme.typography.fontWeightBold
  })
);

const Attachment = styled(Box, { slot: 'Attachment' })(({ theme }) => ({
  width: '100%',
  display: 'flex',
  flexWrap: 'wrap',
  justifyContent: 'space-between',
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column'
  }
}));

const AttachmentItemWrapper = styled(Box, { slot: 'AttachmentItemWrapper' })(
  ({ theme }) => ({
    marginTop: theme.spacing(2),
    flexGrow: 0,
    flexShrink: 0,
    flexBasis: 'calc(50% - 8px)',
    minWidth: 300,
    maxWidth: '100%'
  })
);

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
  margin: theme.spacing(2, 0, 3)
}));

export default function QuizDetail({
  item,
  user,
  questions,
  actions,
  blockProps,
  identity,
  attachments,
  handleAction,
  state,
  name
}) {
  const {
    ItemActionMenu,
    ItemDetailInteraction,
    i18n,
    dispatch,
    useSession,
    jsxBackend,
    dialogBackend,
    useGetItems,
    useGetItem
  } = useGlobal();
  const { user: authUser } = useSession();

  const [resultSubmit, setQuizResult] = React.useState<Record<string, number>>(
    {}
  );
  const results = useSelector<GlobalState>(state =>
    getQuizResultSelector(state, item?.results)
  ) as Record<string, any>;

  const memberResults = useGetItems(item?.member_results);

  const userOwner = useGetItem(item?.user);

  const PendingCard = jsxBackend.get('core.itemView.pendingReviewCard');

  if (!item || !user) return null;

  const {
    statistic,
    id: quizId,
    is_featured,
    is_sponsor,
    is_pending,
    extra
  } = item;

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

  const cover = getImageSrc(item.image, 'origin');

  const handleSetQuiz = value => {
    if (!authUser) {
      dispatch({
        type: 'user/showDialogLogin'
      });

      return;
    }

    setQuizResult(value);
  };

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

  const canPlay = !results && !is_pending && extra?.can_play;

  const isCanViewVoteAnswer =
    Boolean(statistic?.total_play) &&
    (results ? true : item?.extra?.can_view_results_before_answer);

  return (
    <Block data-testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <Box>
          {cover ? (
            <Box sx={{ position: 'relative', overflow: 'hidden' }}>
              <BgCover style={{ backgroundImage: `url(${cover})` }}></BgCover>
              <BgCoverBlur style={{ backgroundImage: `url(${cover})` }} />
            </Box>
          ) : null}
          <ViewContainer>
            {PendingCard && <PendingCard sxWrapper={{ mb: 1 }} item={item} />}
            <ContentWrapper>
              <Box display="flex">
                <ItemTitle
                  variant="h3"
                  component={'div'}
                  showFull
                  sx={{ flex: 1 }}
                >
                  <FeaturedFlag variant="itemView" value={is_featured} />
                  <SponsorFlag
                    variant="itemView"
                    value={is_sponsor}
                    item={item}
                  />
                  <Typography
                    component="h1"
                    variant="h3"
                    sx={{
                      pr: 2.5,
                      display: { sm: 'inline', xs: 'block' },
                      mt: { sm: 0, xs: 1 },
                      verticalAlign: 'middle'
                    }}
                  >
                    {item?.title}
                  </Typography>
                </ItemTitle>
                <ActionMenu>
                  <ItemActionMenu
                    menuName="detailActionMenu"
                    identity={identity}
                    icon={'ico-dottedmore-vertical-o'}
                    state={state}
                    handleAction={handleAction}
                  />
                </ActionMenu>
              </Box>
              <AuthorInfo item={item} statisticDisplay={false} />
              {item?.text && (
                <ItemContent>
                  <RichTextViewMore maxHeight="300px">
                    <HtmlViewerWrapper>
                      <HtmlViewer html={item.text} />
                    </HtmlViewerWrapper>
                  </RichTextViewMore>
                </ItemContent>
              )}
              {attachments?.length > 0 && (
                <Box data-testid={camelCase('view attachments')}>
                  <AttachmentTitle>
                    {i18n.formatMessage({ id: 'attachments' })}
                  </AttachmentTitle>
                  <Attachment>
                    {attachments.map((item, index) => (
                      <AttachmentItemWrapper
                        key={item.id.toString()}
                        data-testid={camelCase(`item attachments ${index + 1}`)}
                      >
                        <AttachmentItem
                          fileName={item.file_name}
                          downloadUrl={item.download_url}
                          isImage={item.is_image}
                          fileSizeText={item.file_size_text}
                          image={item?.image}
                          identity={item?._identity}
                          size="large"
                          index={index}
                        />
                      </AttachmentItemWrapper>
                    ))}
                  </Attachment>
                </Box>
              )}
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
                <ButtonWrapper
                  disabled={isEmpty(resultSubmit)}
                  variant="contained"
                  color="primary"
                  action={handleSubmit}
                  data-testid={camelCase('button_submit')}
                >
                  {i18n.formatMessage({ id: 'submit' })}
                </ButtonWrapper>
              )}
              <Result>
                {results && (
                  <Box data-testid={camelCase('answered_questions')}>
                    {i18n.formatMessage(
                      { id: 'you_have_correct_answer' },
                      {
                        result: () => <strong>{results?.result_correct}</strong>
                      }
                    )}
                  </Box>
                )}
                <Count>
                  <PlayStyled
                    isCanViewVoteAnswer={isCanViewVoteAnswer}
                    data-testid={camelCase('button_total_play')}
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
              </Result>
              <ItemDetailInteraction
                identity={identity}
                state={state}
                handleAction={handleAction}
              />
            </ContentWrapper>
          </ViewContainer>
        </Box>
      </BlockContent>
    </Block>
  );
}
