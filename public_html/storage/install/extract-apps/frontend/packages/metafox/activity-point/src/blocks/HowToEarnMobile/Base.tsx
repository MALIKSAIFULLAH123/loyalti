import { APP_ACTIVITY } from '@metafox/activity-point';
import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { styled, Box } from '@mui/material';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';

export type Props = BlockViewProps;

const BoxRow = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between'
}));

const LeftRow = styled(Box)(({ theme }) => ({
  fontWeight: theme.typography.fontWeightSemiBold,
  marginBottom: theme.spacing(2),
  color: theme.palette.text.primary
}));

const RightRow = styled(Box)(({ theme }) => ({
  marginBottom: theme.spacing(2)
}));

const BoxContainer = styled(Box)(({ theme }) => ({
  padding: theme.spacing(2, 2, 0, 2),
  margin: theme.spacing(0, 2, 1, 2),
  backgroundColor: theme.palette.background.default,
  borderRadius: theme.shape.borderRadius,
  color: theme.palette.text.secondary
}));

const BoxContent = styled(Box)(({ theme }) => ({
  '&:last-child': {
    paddingBottom: theme.spacing(2)
  },
  fontSize: 15
}));

const BoxLabel = styled(Box)(({ theme }) => ({
  padding: theme.spacing(2, 0, 1, 2),
  fontWeight: theme.typography.fontWeightSemiBold,
  color: theme.palette.text.primary
}));

export default function Base({
  title,
  emptyPage = 'core.block.no_content_with_icon',
  ...rest
}: Props) {
  const { useFetchDetail, usePageParams, i18n, jsxBackend } = useGlobal();
  const pageParams = usePageParams();

  const dataSource = useResourceAction(
    APP_ACTIVITY,
    'activitypoint_setting',
    'viewAll'
  );
  const [data, loading, error] = useFetchDetail({
    dataSource,
    pageParams: { id: pageParams.authId }
  });

  let content = null;

  const { packages } = Object.assign({}, data);

  if (packages?.length)
    content = (
      <>
        {packages.map((item, index) => (
          <BoxContent key={index}>
            <BoxLabel>{item.action_label}</BoxLabel>

            {item.settings.map((row, indexof) => (
              <BoxContainer key={indexof}>
                <Box sx={{ mb: 2 }}>{row.description}</Box>
                <BoxRow>
                  <LeftRow>
                    {i18n.formatMessage({ id: 'earned_points' })}
                  </LeftRow>
                  <RightRow>{row.points}</RightRow>
                </BoxRow>
                <BoxRow>
                  <LeftRow>
                    {i18n.formatMessage({ id: 'max_earned_points' })}
                  </LeftRow>
                  <RightRow>{row.max_earned}</RightRow>
                </BoxRow>
                <BoxRow>
                  <LeftRow>{i18n.formatMessage({ id: 'period_day' })}</LeftRow>
                  <RightRow>{row.period}</RightRow>
                </BoxRow>
              </BoxContainer>
            ))}
          </BoxContent>
        ))}
      </>
    );

  return (
    <Block testid="activityPointBlock" {...rest}>
      <BlockHeader title={title} />
      <BlockContent>
        <ErrorBoundary
          loading={loading}
          error={error}
          emptyComponent={
            !packages?.length
              ? React.createElement(jsxBackend.get(emptyPage), {
                  image: 'ico-file-text-o',
                  description: i18n.formatMessage({
                    id: 'can_not_earn_points_at_this_time'
                  }),
                  title: i18n.formatMessage({ id: 'no_results_found' })
                })
              : undefined
          }
        >
          {content}
        </ErrorBoundary>
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'ActivityPoint_HowToEarn';
