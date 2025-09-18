/**
 * @type: itemView
 * name: marketplace_invoice.itemView.invoice
 * chunkName: marketplace
 */

import { useGetItem, Link, useGlobal } from '@metafox/framework';
import {
  APP_MARKETPLACE,
  RESOURCE_INVOICE,
  STAB_BOUGHT
} from '@metafox/marketplace/constants';
import {
  TruncateText,
  ItemView,
  UserName,
  InvoiceStatusLabel
} from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { Box, Grid, styled, Typography } from '@mui/material';
import moment from 'moment';
import * as React from 'react';
import SkeletonLoading from './LoadingSkeleton';

const ItemWrapperStyled = styled(Box, { name: 'ItemStyled' })(({ theme }) => ({
  fontSize: 15,
  color: theme.palette.text.secondary,
  marginTop: theme.spacing(2),
  backgroundColor: theme.palette.background.default,
  borderRadius: theme.shape.borderRadius,
  display: 'flex',
  width: '100%',
  padding: theme.spacing(2),
  flexDirection: 'column',
  position: 'relative'
}));

const WrapActionStyled = styled(Box)(({ theme }) => ({
  position: 'absolute',
  right: 8,
  top: 20
}));

const Item = styled(Box, { shouldForwardProp: props => props !== 'center' })<{
  center?: boolean;
}>(({ theme, center }) => ({
  ...(center && {
    textAlign: 'center'
  })
}));

const RowStyled = styled(Box, {
  slot: 'row',
  shouldForwardProp: props => props !== 'center' && props !== 'noMargin'
})<{ center?: boolean; noMargin?: boolean }>(({ theme, center, noMargin }) => ({
  marginTop: theme.spacing(1.25),
  ...(center && {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center'
  }),
  ...(noMargin && {
    margin: 0
  })
}));
const TitleRowStyle = styled(Typography, { slot: 'title' })(
  ({ theme }) => ({})
);
const ValueRowStyled = styled(Box, { slot: 'value' })(({ theme }) => ({}));

const BoughtInvoice = ({ identity, wrapAs, wrapProps }: any) => {
  const {
    ItemActionMenu,
    useActionControl,
    useResourceMenu,
    usePageParams,
    useIsMobile,
    i18n
  } = useGlobal();
  const isMobile = useIsMobile();
  const pageParams = usePageParams();
  const item = useGetItem(identity);
  const listingItem = useGetItem(item?.listing);
  const seller = useGetItem(item?.seller);
  const buyer = useGetItem(item?.buyer);
  const [handleAction] = useActionControl<unknown, unknown>(identity, {});

  const menus: any = useResourceMenu(
    APP_MARKETPLACE,
    RESOURCE_INVOICE,
    'itemActionMenu'
  );

  if (!item) return null;

  const { status, status_label, link, payment_date, price } = item;

  const { title } = listingItem || {};

  const to = link || `/marketplace/invoice/${item.id}`;

  const itemMenus = filterShowWhen(menus?.items || [], {
    item
  });

  if (isMobile) {
    return (
      <ItemWrapperStyled>
        <WrapActionStyled>
          <ItemActionMenu
            items={itemMenus}
            icon={'ico-dottedmore-vertical-o'}
            handleAction={handleAction}
            size="smaller"
          />
        </WrapActionStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'listing' })}
          </TitleRowStyle>
          <ValueRowStyled>
            <TruncateText lines={2} sx={{ pr: 2 }}>
              <Link to={to} underline="none" color="primary">
                {title}
              </Link>
            </TruncateText>
          </ValueRowStyled>
        </RowStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'amount' })}
          </TitleRowStyle>
          <ValueRowStyled>{price}</ValueRowStyled>
        </RowStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'transaction_date' })}
          </TitleRowStyle>
          <ValueRowStyled>
            {payment_date ? moment(payment_date).format('LLL') : ''}
          </ValueRowStyled>
        </RowStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({ id: 'payment_status' })}
          </TitleRowStyle>
          <ValueRowStyled>
            {status_label && status ? (
              <InvoiceStatusLabel label={status_label} type={status} />
            ) : null}
          </ValueRowStyled>
        </RowStyled>
        <RowStyled>
          <TitleRowStyle variant="h5">
            {i18n.formatMessage({
              id: pageParams?.view === STAB_BOUGHT ? 'seller' : 'buyer'
            })}
          </TitleRowStyle>
          <ValueRowStyled>
            {pageParams?.view === STAB_BOUGHT ? (
              <UserName
                color="none"
                to={seller?.link}
                user={seller}
                hoverCard={false}
              />
            ) : (
              <UserName
                color="none"
                to={buyer?.link}
                user={buyer}
                hoverCard={false}
              />
            )}
          </ValueRowStyled>
        </RowStyled>
      </ItemWrapperStyled>
    );
  }

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="invoice">
      <Grid container alignItems="center">
        <Grid item xs={2.5}>
          <Link to={to} underline="none" color="primary">
            <TruncateText lines={2} sx={{ pr: 2 }}>
              {title}
            </TruncateText>
          </Link>
        </Grid>
        <Grid item xs={2}>
          <Item>{price}</Item>
        </Grid>
        <Grid item xs={2.5}>
          {payment_date ? moment(payment_date).format('LLL') : ''}
        </Grid>
        <Grid item xs={2}>
          {status_label && status ? (
            <InvoiceStatusLabel label={status_label} type={status} />
          ) : null}
        </Grid>
        <Grid item xs={2.5}>
          {pageParams?.view === STAB_BOUGHT ? (
            <UserName
              color="none"
              to={seller?.link}
              user={seller}
              hoverCard={false}
            />
          ) : (
            <UserName
              color="none"
              to={buyer?.link}
              user={buyer}
              hoverCard={false}
            />
          )}
        </Grid>
        <Grid item xs={0.5}>
          <Item center>
            <ItemActionMenu
              items={itemMenus}
              icon={'ico-dottedmore-vertical-o'}
              handleAction={handleAction}
              size="smaller"
            />
          </Item>
        </Grid>
      </Grid>
    </ItemView>
  );
};

BoughtInvoice.LoadingSkeleton = SkeletonLoading;

export default BoughtInvoice;
