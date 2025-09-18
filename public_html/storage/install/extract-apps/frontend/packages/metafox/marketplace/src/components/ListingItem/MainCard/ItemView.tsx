import { Link, useGlobal } from '@metafox/framework';
import {
  FeaturedFlag,
  Image,
  ItemAction,
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  SponsorFlag,
  PendingFlag,
  Flag,
  LineIcon
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { styled, Box } from '@mui/material';
import * as React from 'react';
import { ListingItemProps } from '@metafox/marketplace/types';
import { useSelector } from 'react-redux';
import { APP_MARKETPLACE } from '@metafox/marketplace/constants';
import { get } from 'lodash';

const name = 'ListingMainCardItem';

const FlagWrapper = styled('div', {
  name,
  slot: 'FlagWrapper'
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-end',
  position: 'absolute',
  top: theme.spacing(2),
  right: 0,
  zIndex: 1
}));
const FlagExpires = styled('div', {
  name,
  slot: 'FlagExpires'
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-end',
  position: 'absolute',
  bottom: theme.spacing(2),
  right: 0,
  zIndex: 1
}));
const Price = styled('div', {
  name,
  slot: 'Price'
})(({ theme }) => ({
  color: theme.palette.error.main,
  fontSize: theme.mixins.pxToRem(18),
  fontWeight: theme.typography.fontWeightBold
}));
const ItemMediaWrapper = styled(ItemMedia, {
  name,
  slot: 'ItemMediaWrapper'
})(({ theme }) => ({
  position: 'relative',
  cursor: 'pointer'
}));

const PriceNotAvailable = styled('div', { name, slot: 'PriceNotAvailable' })(
  ({ theme }) => ({
    color: theme.palette.text.hint,
    fontSize: theme.mixins.pxToRem(13),
    fontWeight: 'normal',
    '& span': {
      marginLeft: theme.spacing(0.5)
    }
  })
);

const ListingMainCardItem = ({
  item,
  itemProps,
  identity,
  handleAction,
  state,
  wrapAs,
  wrapProps
}: ListingItemProps) => {
  const { i18n, ItemActionMenu, assetUrl, jsxBackend, dispatch } = useGlobal();
  const { media } = itemProps || {};

  const itemActive = useSelector(state =>
    get(state, `${APP_MARKETPLACE}.${APP_MARKETPLACE}Active`)
  );

  if (!item) return null;

  const { itemOnMap } = itemProps;

  const {
    id,
    image,
    link,
    title,
    price,
    location,
    is_sold,
    is_expired,
    is_free,
    expires_label_item
  } = item;

  const onClickItem = id => {
    if (itemOnMap && `${APP_MARKETPLACE}Active${id}` !== itemActive)
      dispatch({ type: `${APP_MARKETPLACE}/hover`, payload: id });
  };

  const cover = getImageSrc(image, '500', assetUrl('marketplace.no_image'));

  return (
    <ItemView
      id={`${APP_MARKETPLACE}Active${id}`}
      wrapAs={wrapAs}
      onClick={() => onClickItem(`${APP_MARKETPLACE}Active${id}`)}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      identity={identity}
    >
      <ItemMediaWrapper alt={item.title}>
        <FlagWrapper>
          <FeaturedFlag variant="itemView" value={item.is_featured} />
          <SponsorFlag variant="itemView" value={item.is_sponsor} item={item} />
          <PendingFlag variant="itemView" value={item.is_pending} />
          {is_sold || is_expired ? (
            <Box mr={0}>
              {jsxBackend.render({
                component: 'marketplace.ui.soldLabel',
                props: {
                  label: is_expired ? 'expired' : ''
                }
              })}
            </Box>
          ) : null}
        </FlagWrapper>
        {expires_label_item ? (
          <FlagExpires>
            <Flag
              variant="itemView"
              value
              color="white"
              text={i18n.formatMessage({ id: expires_label_item })}
              type="is_expires"
              data-testid="expires"
            />
          </FlagExpires>
        ) : null}
        {itemOnMap ? (
          <Image src={cover} backgroundImage {...media} />
        ) : (
          <Link to={link} identityTracking={identity}>
            <Image src={cover} backgroundImage {...media} />
          </Link>
        )}
      </ItemMediaWrapper>
      <ItemText>
        <ItemTitle
          color={
            itemOnMap && `${APP_MARKETPLACE}Active${id}` === itemActive
              ? 'primary'
              : 'inherit'
          }
        >
          <Link to={link} identityTracking={identity} color={'inherit'}>
            {title}
          </Link>
        </ItemTitle>
        <Box sx={{ display: 'flex', alignItems: 'center', flexFlow: 'wrap' }}>
          {is_free ? (
            <Price> {i18n.formatMessage({ id: 'free' })} </Price>
          ) : (
            <Price
              children={
                price ?? (
                  <PriceNotAvailable>
                    {i18n.formatMessage({ id: 'price_is_not_available' })}
                    <LineIcon icon="ico-question-circle" />
                  </PriceNotAvailable>
                )
              }
            />
          )}
        </Box>
        <ItemSummary>{location?.address}</ItemSummary>
      </ItemText>
      {itemProps.showActionMenu ? (
        <ItemAction sx={{ position: 'absolute', bottom: 8, right: 0 }}>
          <ItemActionMenu
            identity={identity}
            icon={'ico-dottedmore-vertical-o'}
            state={state}
            handleAction={handleAction}
          />
        </ItemAction>
      ) : null}
    </ItemView>
  );
};

ListingMainCardItem.displayName = 'MarketPlaceItemViewMainCard';

export default ListingMainCardItem;
