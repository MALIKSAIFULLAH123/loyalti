import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { Box, Divider, styled, Tooltip, Typography } from '@mui/material';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import ErrorPage from '@metafox/core/pages/ErrorPage/Page';
import { compactUrl } from '@metafox/utils';
import { APP_EWALLET, EWALLET_STATISTIC } from '../../constants';
import { LineIcon } from '@metafox/ui';
import { isEmpty } from 'lodash';

export type Props = BlockViewProps;

const UserBalanceText = styled(Box)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(20),
  fontWeight: 'bold',
  marginBottom: theme.spacing(1)
}));

const UserBalanceNumber = styled(Box)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(40),
  marginBottom: theme.spacing(2)
}));

const Balances = styled(Box, {
  name: 'Emoney',
  slot: 'balances',
  overridesResolver(props, styles) {
    return [styles.balances];
  }
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(16),
  background: theme.palette.border.secondary,
  padding: theme.spacing(2),
  width: '100%',
  maxWidth: '400px',
  borderRadius: theme.spacing(1),
  marginBottom: theme.spacing(2),
  display: 'flex',
  flexDirection: 'column',
  gap: theme.spacing(1)
}));
const Balance = styled(Box)(({ theme }) => ({
  display: 'flex'
}));

const Dotted = styled(Box)(({ theme }) => ({
  flex: 1,
  borderBottom: '2px dotted',
  margin: theme.spacing(0, 0.5, 0.5, 0.5)
}));

const Total = styled(Box)(({ theme }) => ({
  display: 'flex',
  flexFlow: 'wrap',
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column'
  }
}));

const ItemLabel = styled(Box)(({ theme }) => ({
  whiteSpace: 'nowrap',
  marginBottom: theme.spacing(2)
}));

const Item = styled(Box, {
  name: 'Emoney',
  slot: 'itemBalance',
  overridesResolver(props, styles) {
    return [styles.itemBalance];
  }
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  fontSize: theme.mixins.pxToRem(18),
  width: 'auto',
  paddingRight: theme.spacing(2),
  [theme.breakpoints.up('lg')]: {
    width: '50%'
  },
  [theme.breakpoints.down('lg')]: {
    width: '50%'
  },
  [theme.breakpoints.down('md')]: {
    width: '100%'
  },
  [theme.breakpoints.down('sm')]: {
    paddingRight: theme.spacing(0)
  }
}));

const TTL = 36000000;
// cache 1 hours

const ConversionRate = ({ data }) => {
  const { i18n } = useGlobal();

  if (isEmpty(data)) return null;

  return (
    <Box>
      {data.map(item => (
        <Box key={item.price}>
          {i18n.formatMessage(
            { id: 'conversion_rate' },
            {
              base_currency: item.base_currency,
              price: item.price,
              target_currency: item.target_currency
            }
          )}
        </Box>
      ))}
    </Box>
  );
};

export default function Base({
  title,
  emptyPage = 'core.block.no_results',
  ...rest
}: Props) {
  const { useFetchDetail, i18n, useSession } = useGlobal();
  const { user } = useSession();

  const dataSource = useResourceAction(
    APP_EWALLET,
    EWALLET_STATISTIC,
    'viewItem'
  );

  const [data, loading, error] = useFetchDetail({
    dataSource: {
      apiUrl: compactUrl(dataSource.apiUrl, user)
    },
    ttl: TTL,
    cachePrefix: 'dashboard',
    cacheKey: 'ewallet',
    forceReload: false
  });

  return (
    <Block testid="activityPointBlock" {...rest}>
      <BlockHeader title={title} />
      <BlockContent>
        <Box p={{ xs: 2, md: 4 }}>
          <ErrorPage loading={loading} error={error}>
            {data && (
              <>
                <UserBalanceText>
                  {i18n.formatMessage({ id: 'balance' })}
                </UserBalanceText>
                <UserBalanceNumber>{data?.user_balance}</UserBalanceNumber>
                <Box marginBottom={2} sx={{ display: 'inline-block' }}>
                  {data?.balance_description}
                  {!isEmpty(data?.exchange_rates) && (
                    <Tooltip
                      title={<ConversionRate data={data?.exchange_rates} />}
                    >
                      <LineIcon sx={{ ml: 1 }} icon="ico-question-circle-o" />
                    </Tooltip>
                  )}
                </Box>
                <Box mb={2}>
                  {i18n.formatMessage({ id: 'ewallet_available' })}
                </Box>
                <Balances>
                  {data?.balances.map((balance, index) => (
                    <Balance key={index}>
                      {balance?.label}
                      <Dotted sx={{ flex: 1 }}></Dotted>
                      {balance?.value}
                    </Balance>
                  ))}
                </Balances>
                <Divider sx={{ mb: 2 }} />
                <Total>
                  <Item>
                    <ItemLabel>
                      <Typography variant="body1">
                        {i18n.formatMessage({ id: 'total_amount_in' })}
                      </Typography>
                    </ItemLabel>
                    <Balances>
                      {data?.earned.map((balance, index) => (
                        <Balance key={index}>
                          {balance?.label}
                          <Dotted sx={{ flex: 1 }}></Dotted>
                          {balance?.value}
                        </Balance>
                      ))}
                    </Balances>
                  </Item>
                  <Item>
                    <ItemLabel>
                      <Typography variant="body1">
                        {i18n.formatMessage({ id: 'withdrawn' })}
                      </Typography>
                    </ItemLabel>
                    <Balances>
                      {data?.withdraw.map((balance, index) => (
                        <Balance key={index}>
                          {balance?.label}
                          <Dotted sx={{ flex: 1 }}></Dotted>
                          {balance?.value}
                        </Balance>
                      ))}
                    </Balances>
                  </Item>
                  <Item>
                    <ItemLabel>
                      <Typography variant="body1">
                        {i18n.formatMessage({ id: 'pending_withdrawal' })}
                      </Typography>
                    </ItemLabel>
                    <Balances>
                      {data?.pending_withdraw.map((balance, index) => (
                        <Balance key={index}>
                          {balance?.label}
                          <Dotted sx={{ flex: 1 }}></Dotted>
                          {balance?.value}
                        </Balance>
                      ))}
                    </Balances>
                  </Item>
                  <Item>
                    <ItemLabel>
                      <Typography variant="body1">
                        {i18n.formatMessage({ id: 'pending_amount_in' })}
                      </Typography>
                    </ItemLabel>
                    <Balances>
                      {data?.pending_transaction.map((balance, index) => (
                        <Balance key={index}>
                          {balance?.label}
                          <Dotted sx={{ flex: 1 }}></Dotted>
                          {balance?.value}
                        </Balance>
                      ))}
                    </Balances>
                  </Item>
                  <Item>
                    <ItemLabel>
                      <Typography variant="body1">
                        {i18n.formatMessage({ id: 'purchased' })}
                      </Typography>
                    </ItemLabel>
                    <Balances>
                      {data?.purchased.map((balance, index) => (
                        <Balance key={index}>
                          {balance?.label}
                          <Dotted sx={{ flex: 1 }}></Dotted>
                          {balance?.value}
                        </Balance>
                      ))}
                    </Balances>
                  </Item>
                  <Item>
                    <ItemLabel>
                      <Typography variant="body1">
                        {i18n.formatMessage({ id: 'ewallet_sent_by_admin' })}
                      </Typography>
                    </ItemLabel>
                    <Balances>
                      {data?.sent_by_admin.map((balance, index) => (
                        <Balance key={index}>
                          {balance?.label}
                          <Dotted sx={{ flex: 1 }}></Dotted>
                          {balance?.value}
                        </Balance>
                      ))}
                    </Balances>
                  </Item>
                  <Item>
                    <ItemLabel>
                      <Typography variant="body1">
                        {i18n.formatMessage({ id: 'ewallet_reduced_by_admin' })}
                      </Typography>
                    </ItemLabel>
                    <Balances>
                      {data?.reduced_by_admin.map((balance, index) => (
                        <Balance key={index}>
                          {balance?.label}
                          <Dotted sx={{ flex: 1 }}></Dotted>
                          {balance?.value}
                        </Balance>
                      ))}
                    </Balances>
                  </Item>
                </Total>
              </>
            )}
          </ErrorPage>
        </Box>
      </BlockContent>
    </Block>
  );
}

Base.displayName = 'Ewallet_Dashboard';
