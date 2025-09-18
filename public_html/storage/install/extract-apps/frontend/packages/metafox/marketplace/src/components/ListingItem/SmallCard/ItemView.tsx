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
  SponsorFlag
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { styled } from '@mui/material/styles';
import * as React from 'react';
import { ListingItemProps } from '@metafox/marketplace/types';
import { useBlock } from '@metafox/layout';

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
const Price = styled('div', {
  name,
  slot: 'Price'
})(({ theme }) => ({
  color: theme.palette.error.main,
  fontSize: theme.mixins.pxToRem(18),
  fontWeight: theme.typography.fontWeightBold
}));

const ListingSmallCardItem = ({
  item,
  itemProps,
  identity,
  handleAction,
  state,
  wrapAs,
  wrapProps
}: ListingItemProps) => {
  const { ItemActionMenu, assetUrl } = useGlobal();
  const {
    itemProps: { media }
  } = useBlock();

  if (!item) return null;

  const { image, link, title, price, location } = item;
  const cover = getImageSrc(image, '500', assetUrl('marketplace.no_image'));

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia alt={item.title}>
        <FlagWrapper>
          <FeaturedFlag variant="itemView" value={item.is_featured} />
          <SponsorFlag variant="itemView" value={item.is_sponsor} item={item} />
        </FlagWrapper>
        <Image link={link} src={cover} backgroundImage {...media} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Link to={link}>{title}</Link>
        </ItemTitle>
        <Price>{price}</Price>
        <ItemSummary>{location?.address}</ItemSummary>
      </ItemText>
      {itemProps.showActionMenu ? (
        <ItemAction sx={{ position: 'absolute', bottom: 8, right: 8 }}>
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

ListingSmallCardItem.displayName = 'MarketPlaceItemViewSmallCard';

export default ListingSmallCardItem;
