import { Link, useGlobal } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { Block, BlockContent } from '@metafox/layout';
import { InvoiceDetailViewProps as ItemProps } from '@metafox/subscription/types';
import {
  DotSeparator,
  FlagLabel,
  FormatDate,
  Image,
  LineIcon
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled, Tooltip, Typography } from '@mui/material';
import React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import Transaction from './Transaction';

const name = 'SubscriptionDetailView';

export type Props = ItemProps;

const TransactionContainer = styled(Box, {
  name,
  slot: 'transactionContainer'
})(({ theme }) => ({
  width: '100%'
}));

const ImageWrapper = styled(Box, { name, slot: 'imageWrapper' })(
  ({ theme }) => ({
    width: '100%',
    maxWidth: 100,
    img: {
      width: 100,
      height: 100
    }
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

export default function DetailView({
  user,
  identity,
  item,
  state,
  actions,
  handleAction
}: ItemProps) {
  const { i18n, jsxBackend, assetUrl, useTheme, useIsMobile } = useGlobal();
  const isMobile = useIsMobile();
  const theme = useTheme();

  if (!item) return null;

  const {
    package_title,
    price,
    recurring_price,
    expired_at,
    payment_status,
    payment_status_label,
    upgraded_membership,
    payment_buttons,
    table_fields,
    transactions,
    activated_at,
    expired_description
  } = item;
  const image = getImageSrc(
    item.image,
    '240',
    assetUrl('subscription.no_image')
  );
  const backLink = '/subscription';

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <Box mb={2}>
          <Link color="primary" to={backLink}>
            {i18n.formatMessage({ id: 'back_to_all_subscriptions' })}
          </Link>
        </Box>
        <Box sx={{ display: 'flex' }}>
          <ImageWrapper mr={2}>
            <Image src={image} alt={package_title} aspectRatio="11" />
          </ImageWrapper>
          <Box>
            <Typography variant="h4" color="text.primary">
              {package_title}
            </Typography>
            <Price>
              <Typography
                variant="body1"
                color="primary.main"
                fontWeight="bold"
              >
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
                  sx={{ marginLeft: '4px' }}
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
                  {expired_description ? (
                    <Box sx={{ display: 'inline-flex', marginLeft: '4px' }}>
                      ({expired_description})
                    </Box>
                  ) : null}
                </Typography>
              ) : null}
            </DotSeparator>
            <Box mt={1}>
              <Typography variant="body2" color="text.secondary">
                {i18n.formatMessage({ id: 'acquired_membership' })}
                <Tooltip
                  title={i18n.formatMessage({ id: 'membership_question_mark' })}
                  enterTouchDelay={isMobile ? 0 : 500}
                >
                  <LineIcon sx={{ p: 0.5 }} icon=" ico-question-circle-o" />
                </Tooltip>
                : <b>{upgraded_membership}</b>
              </Typography>
            </Box>
            {payment_buttons && payment_buttons.length ? (
              <Box
                mt={2}
                sx={{
                  button: {
                    marginRight: theme.spacing(1),
                    marginBottom: theme.spacing(1)
                  }
                }}
              >
                {payment_buttons.map((paymentButton, index) =>
                  jsxBackend.render({
                    component: 'subscription.ui.paymentButton',
                    props: {
                      ...paymentButton,
                      identity,
                      key: `k${index}`
                    }
                  })
                )}
              </Box>
            ) : null}
          </Box>
        </Box>
        {transactions && transactions.length ? (
          <TransactionContainer mt={5}>
            <Transaction
              tableFields={table_fields}
              transactions={transactions}
            />
          </TransactionContainer>
        ) : null}
      </BlockContent>
    </Block>
  );
}

DetailView.LoadingSkeleton = LoadingSkeleton;
DetailView.displayName = 'SubscriptionDetailView';
