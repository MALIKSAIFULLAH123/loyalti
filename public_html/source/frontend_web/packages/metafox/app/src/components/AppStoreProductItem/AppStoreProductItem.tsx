import { RouteLink, useGlobal, ButtonLink } from '@metafox/framework';
import React from 'react';
import { StoreProductItemShape as ItemShape } from '@metafox/core/types';
import {
  ItemViewProps,
  LineIcon,
  FormatDate,
  ItemView,
  ItemMedia,
  ItemTitle,
  ItemSummary,
  FormatNumberCompact
} from '@metafox/ui';
import { styled } from '@mui/material/styles';
import { Typography, Box, Rating } from '@mui/material';

const name = 'AppStoreProductItem';

const Title = styled(ItemTitle, { name, slot: 'Title' })(({ theme }) => ({
  display: 'block'
}));
const LinkAuthor = styled(RouteLink, { name, slot: 'LinkAuthor' })(
  ({ theme }) => ({
    display: 'inline-flex'
  })
);
const MainTitle = styled(Box, { name, slot: 'Title' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'flex-start',
  justifyContent: 'space-between'
}));

const FooterContent = styled(Box, { name, slot: 'FooterContent' })(
  ({ theme }) => ({
    borderTop: theme.mixins.border('secondary')
  })
);

const Price = styled(Typography, { name, slot: 'Price' })(({ theme }) => ({
  color: theme.palette.warning.main,
  fontSize: 18,
  lineHeight: 1,
  [theme.breakpoints.down('lg')]: {
    fontSize: 16
  }
}));

const ListItem = styled(ItemView, { name, slot: 'ListItem' })(({ theme }) => ({
  color: theme.palette.text.primary
}));

const OverlayLink = styled(RouteLink, { name, slot: 'OverlayLink' })(
  ({ theme }) => ({
    position: 'absolute',
    left: 0,
    right: 0,
    bottom: 0,
    top: 0,
    zIndex: 2
  })
);

const ItemInfo = ({ icon, label, children }) => {
  return (
    <Typography mr={2} variant="body2" color="text.secondary">
      <Box display="flex" sx={{ alignItems: 'center' }}>
        <Box display="inline- flex" sx={{ alignItems: 'center' }} mr={0.5}>
          <Box sx={{ mr: 1, display: 'inline-flex' }}>
            <LineIcon icon={icon} />
          </Box>
          {label}:
        </Box>
        {children}
      </Box>
    </Typography>
  );
};

export default function ProductItem(props: ItemViewProps<ItemShape>) {
  const { i18n } = useGlobal();
  const { item } = props;

  if (!item) return null;

  const { discount, price, action_button } = item;
  const ratingInfo =
    item?.rated > 0
      ? i18n.formatMessage(
          { id: 'app_detail_rating_info' },
          {
            rated: parseFloat(String(item?.rated || 0)).toFixed(1),
            total_reviews: item?.total_reviews
          }
        )
      : i18n.formatMessage({ id: 'app_detail_no_rating_info' });

  return (
    <ListItem {...props}>
      <Box sx={{ width: '100%', position: 'relative' }}>
        <OverlayLink to={`/app/store/product/${item.id}`} />
        <Box sx={{ display: 'flex', alignItems: 'flex-start' }}>
          <ItemMedia src={item.icon[500]} alt={item.name} backgroundImage />
          <Box sx={{ flex: 1, minWidth: 0 }}>
            <Box>
              <MainTitle mb={1} display="flex">
                <Title>
                  <Box sx={{ flex: 1, minWidth: 0 }}>{item.name}</Box>
                </Title>
                <Box>
                  {action_button ? (
                    <Box
                      sx={{
                        display: 'inline-flex',
                        ml: 2,
                        position: 'relative',
                        zIndex: 2,
                        '& a': {
                          whiteSpace: 'nowrap'
                        }
                      }}
                    >
                      <ButtonLink
                        to={`/app/store/product/${item.id}`}
                        variant="outlined"
                        size="small"
                        {...action_button}
                      >
                        {action_button?.label}
                      </ButtonLink>
                    </Box>
                  ) : null}
                </Box>
              </MainTitle>
              <Box
                sx={{ display: 'flex', alignItems: 'center', flexFlow: 'wrap' }}
              >
                <Box
                  color="text.secondary"
                  title={ratingInfo}
                  component={'div'}
                  sx={{
                    display: 'inline-flex',
                    position: 'relative',
                    zIndex: 3
                  }}
                >
                  <Rating
                    name="rating"
                    defaultValue={item?.rated}
                    precision={0.1}
                    readOnly
                    size="small"
                    sx={{ color: 'inherit' }}
                  />
                </Box>
                <Box display="flex" sx={{ alignItems: 'center', pl: 2 }}>
                  {discount && (
                    <Typography
                      sx={{ textDecoration: 'line-through' }}
                      color="text.hint"
                      variant="body1"
                    >
                      ${price}
                    </Typography>
                  )}
                  <Price sx={{ pl: 0.5 }}>
                    {Number(price)
                      ? `$${Number(discount || price)}`
                      : i18n.formatMessage({ id: 'free' })}
                  </Price>
                </Box>
                <Box sx={{ marginLeft: 'auto' }}>
                  <Typography
                    color="text.secondary"
                    variant="body2"
                    sx={{ textAlign: 'right' }}
                  >
                    {i18n.formatMessage(
                      { id: 'total_amount_installed' },
                      {
                        value: (
                          <FormatNumberCompact
                            simple
                            value={item?.total_installed}
                          />
                        )
                      }
                    )}
                  </Typography>
                </Box>
              </Box>
            </Box>

            <Box mt={1}>
              <ItemSummary>{item.description}</ItemSummary>
            </Box>
          </Box>
        </Box>
        <FooterContent pt={1} mt={1}>
          <ItemInfo
            icon={'ico-user3-o'}
            label={i18n.formatMessage({ id: 'app_author' })}
          >
            <LinkAuthor
              to={item.author.url}
              target="_blank"
              sx={{ color: 'inherit', position: 'relative', zIndex: 3 }}
            >
              {item.author.name}
            </LinkAuthor>
          </ItemInfo>
          <Box sx={{ display: 'flex', flexFlow: 'wrap' }}>
            <Box mt={0.5}>
              <ItemInfo
                icon={'ico-arrow-up-circle-o'}
                label={i18n.formatMessage({ id: 'last_updated' })}
              >
                <FormatDate
                  data-testid="updated_at"
                  value={item?.updated_at}
                  format="LL"
                />
              </ItemInfo>
            </Box>
            <Box mt={0.5}>
              <ItemInfo
                icon={'ico-check-circle-o'}
                label={i18n.formatMessage({ id: 'app_compatible' })}
              >
                {item.compatible}
              </ItemInfo>
            </Box>
          </Box>
        </FooterContent>
      </Box>
    </ListItem>
  );
}
