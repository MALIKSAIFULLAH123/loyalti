/* eslint-disable no-restricted-globals */
import { Link, useGlobal } from '@metafox/framework';
import { MarketplaceInvoiceItemProps } from '@metafox/marketplace/types';
import {
  DotSeparator,
  FormatDate,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  InvoiceStatusLabel
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled, Typography } from '@mui/material';
import React from 'react';

const Wrapper = styled(Box, { slot: 'Wrapper' })(() => ({
  display: 'flex',
  flexDirection: 'column',
  overflow: 'hidden',
  position: 'relative',
  maxWidth: '100%',
  flex: '1 1 0%'
}));

const ItemViewWrapper = styled(ItemView, { slot: 'ItemViewWrapper' })(
  ({ theme }) => ({
    [theme.breakpoints.down('sm')]: {
      alignItems: 'flex-start'
    }
  })
);

const ItemMediaWrapper = styled(ItemMedia, { slot: 'ItemMediaWrapper' })(
  () => ({
    width: '100%',
    maxWidth: 100
  })
);

const Price = styled('div', { slot: 'Price' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  marginTop: theme.spacing(1),
  [theme.breakpoints.down('sm')]: {
    alignItems: 'flex-start',
    flexDirection: 'column',
    '& p': {
      marginLeft: '0',
      flexWrap: 'wrap',
      height: 'auto',
      justifyContent: 'flex-start',
      marginBottom: theme.spacing(1)
    }
  }
}));

const ItemTextWrapper = styled(ItemText, { slot: 'ItemTextWrapper' })(
  ({ theme }) => ({
    flexBasis: '100%',
    [theme.breakpoints.up('sm')]: {
      flexDirection: 'row',
      alignItems: 'center'
    },
    [theme.breakpoints.down('sm')]: {
      '& button': {
        marginLeft: '0 !important',
        marginTop: theme.spacing(1)
      }
    }
  })
);

export default function MarketplaceInvoiceItemMainCard({
  item,
  identity,
  itemProps,
  user,
  state,
  handleAction,
  wrapAs,
  wrapProps,
  pagingId
}: MarketplaceInvoiceItemProps) {
  const { assetUrl, jsxBackend, useGetItem } = useGlobal();
  const listingItem = useGetItem(item?.listing);

  if (!item) return null;

  const { status, status_label, payment_buttons, link, payment_date, price } =
    item;

  const { title, image } = listingItem || {};

  const imageSrc = getImageSrc(image, '240', assetUrl('marketplace.no_image'));
  const to = link || `/marketplace/invoice/${item.id}`;

  return (
    <ItemViewWrapper
      testid={item.resource_name}
      wrapAs={wrapAs}
      wrapProps={wrapProps}
    >
      <ItemMediaWrapper src={imageSrc} alt={title} link={to} />
      <ItemTextWrapper>
        <Wrapper>
          <ItemTitle>
            <Link to={to}>{title}</Link>
          </ItemTitle>
          <Price>
            <Typography variant="body1" color="primary.main" fontWeight="bold">
              {price}
            </Typography>
          </Price>
          <DotSeparator mt={1}>
            {status_label && status ? (
              <InvoiceStatusLabel label={status_label} type={status} />
            ) : null}
            {payment_date ? (
              <Typography
                component="span"
                variant="body2"
                color="text.secondary"
              >
                <FormatDate
                  data-testid="paymentDate"
                  value={payment_date}
                  format="ll"
                />
              </Typography>
            ) : null}
          </DotSeparator>
        </Wrapper>
        {payment_buttons && payment_buttons.length ? (
          <Box>
            {payment_buttons.map((paymentButton, index) =>
              jsxBackend.render({
                component: 'marketplace.ui.paymentButton',
                props: {
                  ...paymentButton,
                  identity,
                  key: `k${index}`,
                  pagingId
                }
              })
            )}
          </Box>
        ) : null}
      </ItemTextWrapper>
    </ItemViewWrapper>
  );
}
MarketplaceInvoiceItemMainCard.displayName = 'MarketplaceInvoiceItem(MainCard)';
