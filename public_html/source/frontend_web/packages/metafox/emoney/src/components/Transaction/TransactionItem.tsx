/**
 * @type: itemView
 * name: ewallet.itemView.transaction
 * chunkName: ewallet
 */

import {
  connectItemView,
  useGetItem,
  useGlobal,
  useIsMobile
} from '@metafox/framework';
import { Box, Grid, styled } from '@mui/material';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import { ItemView, UserName } from '@metafox/ui';
import moment from 'moment';

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
  marginBottom: theme.spacing(2),
  textAlign: 'right'
}));

const ItemViewMobileStyled = styled(ItemView)(({ theme }) => ({
  display: 'block'
}));

const Transactions = ({
  identity,
  wrapAs,
  wrapProps,
  state,
  handleAction
}: any) => {
  const item = useGetItem(identity);
  const buyer = useGetItem(item?.buyer);
  const isMobile = useIsMobile();
  const { i18n } = useGlobal();

  if (!item) return null;

  const {
    creation_date,
    fee,
    gross,
    net,
    balance,
    reference,
    status,
    current_balance,
    type,
    source
  } = item;

  if (isMobile) {
    return (
      <ItemViewMobileStyled
        wrapAs={wrapAs}
        wrapProps={wrapProps}
        testid="item-transaction-mobile"
      >
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'ewallet_user' })}</LeftRow>
          <RightRow>
            {buyer ? (
              <UserName to={buyer?.link} user={buyer} color="primary" />
            ) : (
              i18n.formatMessage({ id: 'deleted_user' })
            )}
          </RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'gross' })}</LeftRow>
          <RightRow>{gross}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'fee' })}</LeftRow>
          <RightRow>{fee}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'net' })}</LeftRow>
          <RightRow>{net}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'balance' })}</LeftRow>
          <RightRow>{balance}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'current_balance' })}</LeftRow>
          <RightRow>{current_balance}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'source' })}</LeftRow>
          <RightRow>{source}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'action' })}</LeftRow>
          <RightRow>{type}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'reference' })}</LeftRow>
          <RightRow>{reference}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'status' })}</LeftRow>
          <RightRow>{status}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'creation_date' })}</LeftRow>
          <RightRow>{moment(creation_date).format('L')}</RightRow>
        </BoxRow>
      </ItemViewMobileStyled>
    );
  }

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="item-transaction">
      <Grid container alignItems="center">
        <Grid item xs={1} pr={2}>
          {buyer ? (
            <UserName to={buyer?.link} user={buyer} color="primary" />
          ) : (
            i18n.formatMessage({ id: 'deleted_user' })
          )}
        </Grid>
        <Grid item xs={1.1} pr={2}>
          {gross}
        </Grid>
        <Grid item xs={1.1} pr={2}>
          {fee}
        </Grid>
        <Grid item xs={1.1} pr={2}>
          {net}
        </Grid>
        <Grid item xs={1.1} pr={2}>
          {balance}
        </Grid>
        <Grid item xs={1.1} pr={2}>
          {current_balance}
        </Grid>
        <Grid item xs={1} pr={2}>
          {source}
        </Grid>
        <Grid item xs={1.5} pr={2}>
          {type}
        </Grid>
        <Grid item xs={1} pr={2}>
          {reference}
        </Grid>
        <Grid item xs={1} pr={2}>
          {status}
        </Grid>
        <Grid item xs={1} pr={2}>
          {moment(creation_date).format('L')}
        </Grid>
      </Grid>
    </ItemView>
  );
};

Transactions.LoadingSkeleton = LoadingSkeleton;

export default connectItemView(Transactions, () => {});
