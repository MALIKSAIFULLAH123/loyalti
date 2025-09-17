/**
 * @type: itemView
 * name: ewallet.itemView.withdrawal_request
 * chunkName: ewallet
 */

import {
  connectItemView,
  useGetItem,
  useGlobal,
  useIsMobile
} from '@metafox/framework';
import { Box, Grid, styled, Theme } from '@mui/material';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import { ItemView } from '@metafox/ui';
import moment from 'moment';

const PointStyled = styled(Grid, {
  name: 'PointStyled',
  shouldForwardProp: prop => prop !== 'lostColor'
})(({ theme, lostColor }: { theme: Theme; lostColor: boolean }) => ({
  color: lostColor ? theme.palette.error.main : theme.palette.success.main
}));

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

enum LostColor {
  SENT = 3,
  SPENT = 4,
  RETRIEVED = 6
}

const colorMapping = [LostColor.SENT, LostColor.SPENT, LostColor.RETRIEVED];

const Transactions = ({
  identity,
  wrapAs,
  wrapProps,
  state,
  handleAction
}: any) => {
  const item = useGetItem(identity);
  const isMobile = useIsMobile();
  const { i18n, ItemActionMenu } = useGlobal();

  if (!item) return null;

  const {
    creation_date,
    type_id,
    fee,
    total,
    amount,
    withdraw_method,
    status
  } = item;

  if (isMobile) {
    return (
      <ItemViewMobileStyled
        wrapAs={wrapAs}
        wrapProps={wrapProps}
        testid="item-transaction-mobile"
      >
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'gross' })}</LeftRow>
          <RightRow>{total}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'fee' })}</LeftRow>
          <RightRow>{fee}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'net' })}</LeftRow>
          <RightRow>{amount}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'method' })}</LeftRow>
          <RightRow>{withdraw_method}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'status' })}</LeftRow>
          <RightRow>{status}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'creation_date' })}</LeftRow>
          <RightRow>{moment(creation_date).format('L')}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'options' })}</LeftRow>
          <RightRow>
            <ItemActionMenu
              identity={identity}
              icon="ico-gear-o"
              state={state}
              handleAction={handleAction}
            />
          </RightRow>
        </BoxRow>
      </ItemViewMobileStyled>
    );
  }

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="item-transaction">
      <Grid container alignItems="center">
        <Grid item xs={2}>
          {total}
        </Grid>
        <Grid item xs={2}>
          {fee}
        </Grid>
        <Grid item xs={2}>
          {amount}
        </Grid>
        <PointStyled item xs={1.5} lostColor={colorMapping.includes(type_id)}>
          {withdraw_method}
        </PointStyled>
        <Grid item xs={1.5}>
          <Box sx={{ mr: 1 }}>{status}</Box>
        </Grid>

        <Grid item xs={2}>
          {moment(creation_date).format('L')}
        </Grid>
        <Grid item xs={1}>
          <ItemActionMenu
            identity={identity}
            icon="ico-gear-o"
            state={state}
            handleAction={handleAction}
          />
        </Grid>
      </Grid>
    </ItemView>
  );
};

Transactions.LoadingSkeleton = LoadingSkeleton;

export default connectItemView(Transactions, () => {});
