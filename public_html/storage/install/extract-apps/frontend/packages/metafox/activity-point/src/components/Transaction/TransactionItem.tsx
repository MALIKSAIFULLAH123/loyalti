/**
 * @type: itemView
 * name: activitypoint.itemView.transaction
 * chunkName: activityPoint
 */

import { useGetItem, useGlobal, useIsMobile } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
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

const Transactions = ({ identity, wrapAs, wrapProps }: any) => {
  const item = useGetItem(identity);
  const isMobile = useIsMobile();
  const { i18n } = useGlobal();

  if (!item) return null;

  const {
    type_name,
    package_name,
    action,
    id,
    creation_date,
    type_id,
    points
  } = item;

  if (isMobile) {
    return (
      <ItemViewMobileStyled
        wrapAs={wrapAs}
        wrapProps={wrapProps}
        testid="item-transaction-mobile"
      >
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'App' })}</LeftRow>
          <RightRow>{package_name}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'point' })}</LeftRow>
          <RightRow>{points}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'action' })}</LeftRow>
          <RightRow>
            <HtmlViewer html={action} />
          </RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'type' })}</LeftRow>
          <RightRow>{type_name}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'id' })}</LeftRow>
          <RightRow>{id}</RightRow>
        </BoxRow>
        <BoxRow>
          <LeftRow>{i18n.formatMessage({ id: 'date' })}</LeftRow>
          <RightRow>{moment(creation_date).format('L')}</RightRow>
        </BoxRow>
      </ItemViewMobileStyled>
    );
  }

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="item-transaction">
      <Grid container alignItems="center">
        <Grid item xs={2}>
          {package_name}
        </Grid>
        <PointStyled item xs={1.5} lostColor={colorMapping.includes(type_id)}>
          {points}
        </PointStyled>
        <Grid item xs={4.5}>
          <Box sx={{ mr: 1 }}>
            <HtmlViewer html={action} />
          </Box>
        </Grid>
        <Grid item xs={1.5}>
          {type_name}
        </Grid>
        <Grid item xs={1}>
          {id}
        </Grid>
        <Grid item xs={1.5}>
          {moment(creation_date).format('L')}
        </Grid>
      </Grid>
    </ItemView>
  );
};

Transactions.LoadingSkeleton = LoadingSkeleton;

export default Transactions;
