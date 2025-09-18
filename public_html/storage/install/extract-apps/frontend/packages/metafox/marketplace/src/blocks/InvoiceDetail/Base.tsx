import { Link, useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { InvoiceDetailViewProps as ItemProps } from '@metafox/marketplace/types';
import {
  DotSeparator,
  FormatDate,
  Image,
  InvoiceStatusLabel
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled, Typography } from '@mui/material';
import React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import Transaction from './Transaction';

const name = 'MarketplaceDetailView';

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
  const {
    i18n,
    jsxBackend,
    assetUrl,
    useTheme,
    useGetItem,
    useGetItems,
    useSession
  } = useGlobal();
  const theme = useTheme();
  const listingItem = useGetItem(item?.listing);
  const transactions = useGetItems(item?.transactions);

  const { user: userAuth } = useSession();

  const seller = useGetItem(item?.seller);

  if (!item) return null;

  const {
    status,
    status_label,
    payment_buttons,
    payment_date,
    price,
    table_fields
  } = item;

  const { title, image, link } = listingItem || {};
  const imageSrc = getImageSrc(image, '240', assetUrl('marketplace.no_image'));

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <Box mb={2}>
          <Link
            color="primary"
            to={
              seller?.id === userAuth?.id
                ? '/marketplace/invoice-sold'
                : '/marketplace/invoice-bought'
            }
          >
            {i18n.formatMessage({ id: 'back_to_invoices' })}
          </Link>
        </Box>
        <Box sx={{ display: 'flex' }}>
          <ImageWrapper mr={2}>
            <Image link={link} src={imageSrc} alt={title} aspectRatio="11" />
          </ImageWrapper>
          <Box>
            <Typography variant="h4" color="text.primary">
              {link ? <Link to={link}>{title}</Link> : title}
            </Typography>
            <Price>
              <Typography
                variant="body1"
                color="primary.main"
                fontWeight="bold"
              >
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
                    component: 'marketplace.ui.paymentButton',
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
DetailView.displayName = 'MarketplaceDetailView';
