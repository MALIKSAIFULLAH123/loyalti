import { useGlobal } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { PackageItemProps } from '@metafox/subscription/types';
import { Image, ItemView, LineIcon } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, Button, styled, Typography, Tooltip } from '@mui/material';
import React from 'react';

const name = 'PackageItemMainCard';

const PackageOuter = styled(Box, { name, slot: 'packageOuter' })(
  ({ theme }) => ({
    height: '100%'
  })
);

const PackageInner = styled(Box, { name, slot: 'packageInner' })(
  ({ theme }) => ({
    padding: theme.spacing(4),
    display: 'flex',
    flexDirection: 'column',
    height: '100%'
  })
);

const PackageIcon = styled(Box, { name, slot: 'packageIcon' })(({ theme }) => ({
  marginTop: theme.spacing(2.5),
  '& img': {
    width: 100,
    height: 100,
    objectFit: 'cover'
  }
}));

const PackagePrice = styled(Box, { name, slot: 'packagePrice' })(
  ({ theme }) => ({
    color: theme.palette.primary.main,
    fontSize: theme.mixins.pxToRem(40),
    lineHeight: 1
  })
);

const Description = styled(Box, { name, slot: 'packageDescription' })(
  ({ theme }) => ({
    borderTop: 'solid 1px',
    borderTopColor: theme.palette.border?.secondary
  })
);

const ImagePopular = styled(Box, { name, slot: 'imagePopular' })(
  ({ theme }) => ({
    display: 'inline-flex',
    width: '90px',
    position: 'absolute',
    top: '-3px',
    right: '-3px',
    '& img': {
      background: 'transparent',
      borderColor: 'transparent'
    }
  })
);

export default function PackageItemMainCard({
  item,
  identity,
  itemProps,
  user,
  state,
  handleAction,
  actions,
  wrapAs,
  wrapProps
}: PackageItemProps) {
  const { i18n, assetUrl, useSession, useIsMobile } = useGlobal();
  const isMobile = useIsMobile();
  const {
    title,
    price,
    description,
    recurring_price,
    is_purchased,
    is_popular,
    success_role,
    extra
  } = item;
  const { loggedIn } = useSession();
  const image = getImageSrc(
    item.image,
    '240',
    assetUrl('subscription.no_image')
  );

  const popularImg = assetUrl('subscription.popular_image');

  const handlePayment = React.useCallback(() => {
    if (is_purchased) return;

    actions.paymentItem();
  }, [is_purchased, actions]);

  return (
    <ItemView testid="package.mainCard" wrapAs={wrapAs} wrapProps={wrapProps}>
      <PackageOuter>
        <PackageInner>
          {is_popular ? (
            <ImagePopular>
              <Image src={popularImg} />
            </ImagePopular>
          ) : null}
          <Typography component="h4" variant="h4">
            {title}
          </Typography>
          <PackageIcon>
            <img src={image} alt={title} />
          </PackageIcon>
          <PackagePrice mt={3}>{price}</PackagePrice>
          <Box mt={1.5}>
            <Typography
              component="div"
              variant="body1"
              color={'text.secondary'}
            >
              <HtmlViewer html={recurring_price} />
            </Typography>
          </Box>
          <Description mt={2} pt={2} mb={3}>
            {success_role ? (
              <Box mb={2}>
                <Typography variant="body2" color="text.secondary">
                  {i18n.formatMessage({
                    id: is_purchased
                      ? 'acquired_membership'
                      : 'reaching_membership'
                  })}{' '}
                  <Tooltip
                    title={i18n.formatMessage({
                      id: 'membership_question_mark'
                    })}
                    enterTouchDelay={isMobile ? 0 : 500}
                  >
                    <LineIcon sx={{ p: 0.5 }} icon=" ico-question-circle-o" />
                  </Tooltip>
                  : <b>{success_role}</b>
                </Typography>
              </Box>
            ) : null}
            <Typography
              component="div"
              variant="body2"
              color={'text.secondary'}
            >
              {description}
            </Typography>
          </Description>
          {loggedIn && (
            <Box sx={{ mt: 'auto' }}>
              {extra?.can_show_payment_button && (
                <Button
                  variant="outlined"
                  color="primary"
                  data-testid="buttonChoosePackage"
                  onClick={handlePayment}
                  sx={{
                    width: '100%'
                  }}
                >
                  {i18n.formatMessage({ id: 'choose_package' })}
                </Button>
              )}
              {extra?.can_show_no_payment_gateway_message && (
                <Button
                  variant="outlined"
                  color="primary"
                  data-testid="buttonChoosePackage"
                  disabled
                  sx={{
                    width: '100%'
                  }}
                >
                  {i18n.formatMessage({
                    id: 'no_payment_options_available'
                  })}
                </Button>
              )}
              {is_purchased && (
                <Button
                  variant="outlined"
                  color="primary"
                  data-testid="buttonChoosePackage"
                  disabled
                  sx={{
                    width: '100%'
                  }}
                >
                  {i18n.formatMessage({ id: 'already_owned' })}
                </Button>
              )}
            </Box>
          )}
        </PackageInner>
      </PackageOuter>
    </ItemView>
  );
}
PackageItemMainCard.displayName = 'PackageItem(MainCard)';
