/* eslint-disable no-restricted-globals */
import { Link, useGlobal } from '@metafox/framework';
import { InvoiceItemProps } from '@metafox/subscription/types';
import {
  DotSeparator,
  FormatDate,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled, Tooltip, Typography } from '@mui/material';
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

const Price = styled('div', {
  name: 'Subscription',
  slot: 'priceInvoiceItem',
  overridesResolver(props, styles) {
    return [styles.priceInvoiceItem];
  }
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  flexFlow: 'wrap',
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

const PriceText = styled(Typography, {
  name: 'Subscription',
  slot: 'PriceText'
})(({ theme }) => ({
  marginRight: theme.spacing(0.5),
  marginTop: theme.spacing(1)
}));

const ItemTextWrapper = styled(ItemText, { slot: 'ItemTextWrapper' })(
  ({ theme }) => ({
    flexBasis: '100%',
    [theme.breakpoints.up('sm')]: {
      alignItems: 'center',
      '& button': {
        marginTop: '8px !important'
      }
    }
  })
);

export default function InvoiceItemActiveCard({
  item,
  identity,
  itemProps,
  user,
  state,
  handleAction,
  wrapAs,
  wrapProps,
  pagingId
}: InvoiceItemProps) {
  const { assetUrl, i18n, jsxBackend, useIsMobile } = useGlobal();
  const isMobile = useIsMobile();
  const {
    package_title,
    price,
    expired_at,
    payment_status,
    payment_status_label,
    upgraded_membership,
    payment_buttons,
    activated_at
  } = item;
  const image = getImageSrc(
    item.image,
    '240',
    assetUrl('subscription.no_image')
  );
  const to = `/subscription/${item.id}`;

  return (
    <ItemViewWrapper
      testid={item.resource_name}
      wrapAs={wrapAs}
      wrapProps={wrapProps}
    >
      <ItemMediaWrapper src={image} alt={package_title} link={to} />
      <ItemTextWrapper>
        <Wrapper>
          <ItemTitle>
            <Link to={to}>{package_title}</Link>
          </ItemTitle>
          <Price>
            <PriceText variant="body1" color="primary.main" fontWeight="bold">
              {price}
            </PriceText>
          </Price>
          <DotSeparator mt={1}>
            {payment_status_label && payment_status
              ? jsxBackend.render({
                  component: 'subscription.ui.statusLabel',
                  props: {
                    label: payment_status_label,
                    type: payment_status
                  }
                })
              : null}
            {activated_at ? (
              <Typography
                component="span"
                variant="body2"
                color="text.secondary"
              >
                <FormatDate
                  data-testid="expiredDate"
                  value={activated_at}
                  format="ll"
                  phrase="activation_date_time"
                />
                {expired_at ? (
                  <Box sx={{ display: 'inline-flex', marginTop: 0.5 }}>
                    <FormatDate
                      data-testid="expiredDate"
                      value={expired_at}
                      format="ll"
                      phrase="expires_on_time"
                    />
                  </Box>
                ) : null}
              </Typography>
            ) : null}
          </DotSeparator>
          <Box mt={1}>
            <Typography variant="body2" color="text.secondary">
              {i18n.formatMessage({ id: 'acquired_membership' })}{' '}
              <Tooltip
                title={i18n.formatMessage({ id: 'membership_question_mark' })}
                enterTouchDelay={isMobile ? 0 : 500}
              >
                <LineIcon
                  icon="ico-question-circle-o"
                  sx={{ display: 'inline-block', height: '16px' }}
                />
              </Tooltip>{' '}
              : <b>{upgraded_membership}</b>
            </Typography>
          </Box>
        </Wrapper>
        {payment_buttons && payment_buttons.length ? (
          <Box>
            {payment_buttons.map((paymentButton, index) =>
              jsxBackend.render({
                component: 'subscription.ui.paymentButton',
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
InvoiceItemActiveCard.displayName = 'InvoiceItem(MainCard)';
