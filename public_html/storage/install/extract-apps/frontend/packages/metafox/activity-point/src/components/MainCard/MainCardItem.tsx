/**
 * @type: itemView
 * name: activitypoint.itemView.packageItem
 * chunkName: activityPoint
 */

import { useGetItem, useGlobal } from '@metafox/framework';
import { Typography, Box, styled, Button, Card } from '@mui/material';
import React from 'react';
import { Image, ItemView, LineIcon } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';

const ItemViewStyled = styled(ItemView)(({ theme }) => ({
  maxWidth: '100%'
}));

const PackageItemStyled = styled(Card, { name: 'PackageItemStyled' })(
  ({ theme }) => ({
    padding: theme.spacing(4),
    width: '100%',
    height: '100%'
  })
);

const PointStyled = styled(Typography, {
  name: 'ActivityPoint',
  slot: 'activityPointPackages',
  overridesResolver(props, styles) {
    return [styles.activityPointPackages];
  }
})(({ theme }) => ({
  fontWeight: theme.typography.fontWeightRegular,
  color: theme.palette.primary.main,
  marginTop: theme.spacing(3)
}));

const PriceNotAvailable = styled('div', {
  name: 'ActivityPoint',
  slot: 'PriceNotAvailable'
})(({ theme }) => ({
  color: theme.palette.text.hint,
  fontSize: theme.mixins.pxToRem(13),
  fontWeight: 'normal',
  '& span': {
    marginLeft: theme.spacing(0.5)
  }
}));

const BtnPayDisableStyled = styled(Button, { name: 'BtnPayDisable' })(
  ({ theme }) => ({
    backgroundColor: `${theme.palette.grey['100']} !important`
  })
);

const Transactions = ({ identity, ...props }: any) => {
  const { i18n, dispatch, assetUrl } = useGlobal();

  const item = useGetItem(identity);

  if (!item) return null;

  const { extra } = item;

  const handlePurchase = () => {
    dispatch({ type: 'activityPoint/purchase', payload: { identity } });
  };

  return (
    <ItemViewStyled {...props}>
      <PackageItemStyled>
        <Box>
          <Typography variant="subtitle1" sx={{ mb: 2 }}>
            {item.title}
          </Typography>
          <Box sx={{ width: '100px', height: '100px' }}>
            <Image
              src={getImageSrc(
                item.image,
                '120, 500',
                assetUrl('activitypoint.package_no_image')
              )}
              aspectRatio={'11'}
            />
          </Box>
          <PointStyled variant="h1">
            {i18n.formatMessage({ id: 'points' }, { amount: item.amount })}
          </PointStyled>
          <Typography variant="body1" color="text.secondary">
            {item.price_string ?? (
              <PriceNotAvailable>
                {i18n.formatMessage({ id: 'price_is_not_available' })}
                <LineIcon icon="ico-question-circle" />
              </PriceNotAvailable>
            )}
          </Typography>
        </Box>
        {extra?.can_show_payment_button && (
          <Button
            sx={{ mt: 4, width: '100%' }}
            size="medium"
            color="primary"
            variant="outlined"
            onClick={handlePurchase}
          >
            {i18n.formatMessage({ id: 'purchase' })}
          </Button>
        )}
        {extra?.can_show_no_payment_gateway_message && (
          <BtnPayDisableStyled
            sx={{ mt: 4, width: '100%' }}
            disabled
            size="medium"
            color="primary"
            variant="contained"
          >
            {i18n.formatMessage({
              id: 'no_payment_options_available'
            })}
          </BtnPayDisableStyled>
        )}
      </PackageItemStyled>
    </ItemViewStyled>
  );
};

export default Transactions;
