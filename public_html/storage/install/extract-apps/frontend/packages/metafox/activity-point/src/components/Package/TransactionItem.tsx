/**
 * @type: itemView
 * name: activitypoint.itemView.package
 * chunkName: activityPoint
 */

import { useGetItem, useGlobal, useIsMobile } from '@metafox/framework';
import { Box, Grid, styled } from '@mui/material';
import * as React from 'react';
import { ItemView } from '@metafox/ui';
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

const Transactions = ({ identity, wrapAs, wrapProps }: any) => {
  const item = useGetItem(identity);
  const isMobile = useIsMobile();
  const { i18n } = useGlobal();

  if (!item) return null;

  const {
    status,
    package_name,
    package_price_string,
    date,
    package_point,
    id
  } = item;

  if (isMobile) {
    return (
      <ItemViewMobileStyled
        wrapAs={wrapAs}
        wrapProps={wrapProps}
        testid="item-package-mobile"
      >
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'package_name' })}</LeftRow>
          <RightRow>{package_name}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'point' })}</LeftRow>
          <RightRow>{package_point}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'price' })}</LeftRow>
          <RightRow>{package_price_string}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'status' })}</LeftRow>
          <RightRow>{status}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'id' })}</LeftRow>
          <RightRow>{id}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'date' })}</LeftRow>
          <RightRow>{moment(date).format('L')}</RightRow>
        </BoxRow>
      </ItemViewMobileStyled>
    );
  }

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="item-package">
      <Grid container alignItems="center">
        <Grid item xs={3}>
          {package_name}
        </Grid>
        <Grid item xs={1.5}>
          {package_point}
        </Grid>
        <Grid item xs={2}>
          {package_price_string}
        </Grid>
        <Grid item xs={2.5}>
          {status}
        </Grid>
        <Grid item xs={1}>
          {id}
        </Grid>
        <Grid item xs={2}>
          {moment(date).format('L')}
        </Grid>
      </Grid>
    </ItemView>
  );
};

export default Transactions;
