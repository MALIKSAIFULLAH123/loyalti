import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';
import { Link, useGlobal, useResourceAction } from '@metafox/framework';
import { APP_QUIZ, RESOURCE_QUIZ } from '@metafox/quiz/constant';
import { UserAvatar } from '@metafox/ui';
import { Box, styled, Typography } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';
import QuizQuestion from './QuizQuestion';

const WrapInfoStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  marginTop: theme.spacing(2)
}));

const ProfileLinkStyled = styled(Link, {
  name: 'ProfileLinkStyled'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  fontWeight: theme.typography.fontWeightBold,
  paddingRight: theme.spacing(0.5),
  color: theme.palette.text.primary
}));

function IndividualItem({ questions, item, userId }: any) {
  const { useFetchDetail, i18n } = useGlobal();
  const dataSource = useResourceAction(
    APP_QUIZ,
    RESOURCE_QUIZ,
    'viewQuizResultIndividual'
  );

  const [data, loading, error] = useFetchDetail({
    dataSource,
    pageParams: { quiz_id: item.id, user_id: userId }
  });

  const numberAnswerRight = (questions || []).reduce(
    (accumulator, currentValue) => {
      const question = data?.user_result?.find(
        item => item.question_id === currentValue.id
      );

      const rightAnswer = Boolean(
        question?.correct_answer_id === question?.user_answer_id
      );

      if (rightAnswer) {
        return accumulator + 1;
      }

      return accumulator;
    },
    0
  );

  return (
    <ErrorBoundary loading={loading} error={error} emptyComponent={null}>
      {isEmpty(data) ? null : (
        <Box>
          <WrapInfoStyled>
            <UserAvatar user={data?.user} size={48} />
            <Box sx={{ ml: 1.5 }}>
              <ProfileLinkStyled
                to={data?.user?.link}
                children={data?.user.full_name}
                data-testid="headline"
              />
              <Typography color="text.secondary" mt={0.5}>
                {i18n.formatMessage(
                  { id: 'correct_answer_percent_question' },
                  {
                    result: () => (
                      <strong>{`${numberAnswerRight}/${questions?.length}`}</strong>
                    ),
                    percent: () => <strong>({data?.result_correct})</strong>
                  }
                )}
              </Typography>
            </Box>
          </WrapInfoStyled>
          {questions?.length > 0 &&
            questions.map((i, index) => (
              <QuizQuestion
                key={index.toString()}
                question={i.question}
                questionId={i.id}
                answers={i.answers}
                order={index + 1}
                result={data?.user_result?.find(
                  item => item.question_id === i.id
                )}
              />
            ))}
        </Box>
      )}
    </ErrorBoundary>
  );
}

export default IndividualItem;
