/* eslint-disable no-restricted-globals */
import { Link, useGlobal } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { InvoiceItemProps } from '@metafox/subscription/types';
import {
  DotSeparator,
  FlagLabel,
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
    borderRadius: theme.spacing(1),
    [theme.breakpoints.down('sm')]: {
      alignItems: 'flex-start'
    }
  })
);

const ItemMediaWrapper = styled(ItemMedia, { slot: 'ItemMediaWrapper' })(
  () => ({
    width: '100%',
    maxWidth: 60
  })
);

const Price = styled('div', { slot: 'Price' })(({ theme }) => ({
  display: 'flex',
  flexFlow: 'wrap',
  marginTop: theme.spacing(0.5),
  [theme.breakpoints.down('sm')]: {
    alignItems: 'flex-start',
    '& p': {
      marginLeft: '0',
      height: 'auto',
      justifyContent: 'flex-start',
      marginBottom: theme.spacing(1)
    }
  }
}));

const ItemTextWrapper = styled(ItemText, { slot: 'ItemTextWrapper' })(
  ({ theme }) => ({
    flexBasis: '100%',
    flexDirection: 'column',
    alignItems: 'center',
    '& button': {
      marginLeft: '8px !important'
    },
    [theme.breakpoints.down('sm')]: {
      '& button': {
        marginLeft: '0 !important',
        marginRight: '8px !important',
        marginTop: theme.spacing(1)
      }
    }
  })
);

export default function InvoiceItemSmallCard({
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
  const { assetUrl, i18n, jsxBackend, useTheme } = useGlobal();
  const theme = useTheme();
  const {
    package_title,
    price,
    recurring_price,
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
            <Typography variant="body2" color="primary.main" fontWeight="bold">
              {price}
            </Typography>
            {recurring_price ? (
              <FlagLabel
                backgroundColor={
                  theme.palette.mode === 'dark'
                    ? theme.palette.grey[600]
                    : theme.palette.grey[100]
                }
                variant="body2"
                color="text.primary"
              >
                <HtmlViewer html={recurring_price} />
              </FlagLabel>
            ) : null}
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
                  <Box sx={{ display: 'inline-flex', marginLeft: '4px' }}>
                    (
                    <FormatDate
                      data-testid="expiredDate"
                      value={expired_at}
                      format="ll"
                      phrase="expires_on_time"
                    />
                    )
                  </Box>
                ) : null}
              </Typography>
            ) : null}
          </DotSeparator>
          <Box mt={0.5}>
            <Typography variant="body2" color="text.secondary">
              {i18n.formatMessage({ id: 'acquired_membership' })}{' '}
              <Tooltip
                title={i18n.formatMessage({
                  id: 'membership_question_mark'
                })}
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
          <Box sx={{ pt: 1 }}>
            {payment_buttons.map((paymentButton, index) =>
              jsxBackend.render({
                component: 'subscription.ui.paymentButton',
                props: {
                  ...paymentButton,
                  identity,
                  key: `k${index}`,
                  pagingId,
                  size: 'small'
                }
              })
            )}
          </Box>
        ) : null}
      </ItemTextWrapper>
    </ItemViewWrapper>
  );
}
InvoiceItemSmallCard.displayName = 'InvoiceItem(MainCard)';