import { ActivityPointItem, APP_ACTIVITY } from '@metafox/activity-point';
import {
  BlockViewProps,
  ButtonLink,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { Typography, Tooltip, Box, styled } from '@mui/material';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React, { useEffect } from 'react';
import { FormatNumberCompact, LineIcon } from '@metafox/ui';
import ErrorPage from '@metafox/core/pages/ErrorPage/Page';

export type Props = BlockViewProps;

const StatisticPointWrapperStyled = styled(Box, {
  name: 'StatisticPointWrapper'
})(() => ({
  display: 'flex',
  flexWrap: 'wrap',
  marginBottom: '-48px',
  marginTop: '40px'
}));

const StatisticPointStyled = styled(Box, {
   name: 'ActivityPoint',
   slot: 'statisticPoint',
  overridesResolver(props, styles) {
    return [styles.statisticPoint];
  }
})(({ theme }) => ({
    paddingBottom: theme.spacing(6),
    width: '25%',
    [theme.breakpoints.down('sm')]: {
      width: '50%'
    }
  })
);

const Wrapper = styled(Box, { name: 'Wrapper' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  flexWrap: 'wrap',
  '& .MuiAvatar-root': {
    marginRight: theme.spacing(4)
  },
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column',
    alignItems: 'flex-start',
    '& .MuiAvatar-root': {
      marginRight: theme.spacing(0),
      marginBottom: theme.spacing(2)
    }
  }
}));

const NumberStyled = styled(Typography, { name: 'numberStyle' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(40)
  })
);

const ActivityBlock = styled(Box, {
  name: 'ActivityPoint',
  slot: 'activityDashboard',
  overridesResolver(props, styles) {
    return [styles.activityDashboard];
  }
})(({ theme }) => ({
  padding: theme.spacing(4)
}));

const StatisticPoint = ({ data }: { data: ActivityPointItem }) => {
  const { useIsMobile } = useGlobal();
  const isMobile = useIsMobile();
  const { label, value, hint } = data;

  return (
    <StatisticPointStyled>
      <Typography variant="body1" color="text.secondary" sx={{ mb: 1 }}>
        {label}
        <Tooltip title={hint} enterTouchDelay={isMobile ? 0 : 500}>
          <LineIcon sx={{ ml: 1 }} icon=" ico-question-circle-o" />
        </Tooltip>
      </Typography>
      <NumberStyled>
        {value ? <FormatNumberCompact value={value} /> : 0}
      </NumberStyled>
    </StatisticPointStyled>
  );
};

export default function Base({
  title,
  emptyPage = 'core.block.no_results',
  ...rest
}: Props) {
  const { useFetchDetail, usePageParams, i18n, jsxBackend, dispatch } =
    useGlobal();
  const pageParams = usePageParams();

  const dataSource = useResourceAction(
    APP_ACTIVITY,
    APP_ACTIVITY,
    'getStatistic'
  );

  const [data, loading, error, response] = useFetchDetail({
    dataSource,
    pageParams: { id: pageParams.authId, purchase_id: pageParams.purchase_id }
  });

  useEffect(() => {
    if (!response?.data?.message) return;

    dispatch({ type: '@handleActionFeedback', payload: response });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [response]);

  let content = null;

  const { items, user, available_points } = Object.assign({}, data);

  if (!items || !user) content = jsxBackend.render({ component: emptyPage });

  if (items && user)
    content = (
      <>
        <Wrapper>
          <Box width="100%">
            <Typography
              sx={{ fontWeight: 'fontWeightRegular', fontSize: '1.5rem' }}
            >
              {i18n.formatMessage({ id: 'current_points' })}
            </Typography>
            <NumberStyled>
              {available_points ? (
                <FormatNumberCompact value={available_points} />
              ) : (
                0
              )}
            </NumberStyled>
            <Typography variant="body1" color="text.secondary">
              {i18n.formatMessage({ id: 'current_points_description' })}
            </Typography>
          </Box>
          <Box>
            <ButtonLink
              sx={{ mt: 2 }}
              variant="outlined"
              startIcon={<LineIcon icon="ico-text-file-search" />}
              to={'/activitypoint/transactions-history'}
            >
              {i18n.formatMessage({ id: 'view_all_transactions' })}
            </ButtonLink>
          </Box>
        </Wrapper>
        <StatisticPointWrapperStyled>
          {items.map((item, index) => (
            <StatisticPoint data={item} key={index} />
          ))}
        </StatisticPointWrapperStyled>
      </>
    );

  return (
    <Block testid="activityPointBlock" {...rest}>
      <BlockHeader title={title} />
      <BlockContent>
        <ActivityBlock>
          <ErrorPage loading={loading} error={error}>
            {content}
          </ErrorPage>
        </ActivityBlock>
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'ActivityPoint_Dashboard';
