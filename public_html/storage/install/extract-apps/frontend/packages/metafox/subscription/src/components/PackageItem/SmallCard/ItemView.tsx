import { useGlobal } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { PackageItemProps } from '@metafox/subscription/types';
import {
  FlagLabel,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, Button, styled, Typography, Tooltip } from '@mui/material';
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
    width: '100%'
  })
);

const ItemTextWrapper = styled(ItemText, { slot: 'ItemTextWrapper' })(
  ({ theme }) => ({
    flexBasis: '100%',
    '& button': {
      marginLeft: theme.spacing(1)
    },
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

export default function PackageItemSmallCard({
  item,
  identity,
  itemProps,
  user,
  state,
  handleAction,
  wrapAs,
  wrapProps,
  pagingId,
  actions
}: PackageItemProps) {
  const { assetUrl, useTheme, i18n, useSession, useDialog, useIsMobile } = useGlobal();
  const isMobile = useIsMobile();
  const { loggedIn } = useSession();
  const theme = useTheme();
  const { closeDialog } = useDialog();
  const {
    title,
    price,
    recurring_price,
    description,
    id,
    is_purchased,
    success_role
  } = item;
  const image = getImageSrc(
    item.image,
    '240',
    assetUrl('subscription.no_image')
  );

  const handleSelectOnRegister = () => {
    if (closeDialog) {
      closeDialog();
    }

    if (
      'function' === typeof itemProps?.setFieldValue &&
      itemProps?.relatedFieldName
    ) {
      itemProps.setFieldValue(itemProps.relatedFieldName, id, true);
    }
  };

  return (
    <ItemViewWrapper
      testid={item.resource_name}
      wrapAs={wrapAs}
      wrapProps={wrapProps}
    >
      <ItemMediaWrapper src={image} alt={title} />
      <ItemTextWrapper>
        <Wrapper>
          <ItemTitle>{title}</ItemTitle>
          <Price>
            <Typography variant="body1" color="primary.main" fontWeight="bold">
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
          {success_role ? (
            <Box mt={1}>
              <Typography variant="body2" color="text.secondary">
                {i18n.formatMessage({
                  id: is_purchased
                    ? 'acquired_membership'
                    : 'reaching_membership'
                })}{' '}
                <Tooltip
                  title={i18n.formatMessage({ id: 'membership_question_mark' })}
                  enterTouchDelay={isMobile ? 0 : 500}
                >
                  <LineIcon sx={{ p: 0.5 }} icon=" ico-question-circle-o" />
                </Tooltip>
                : <b>{success_role}</b>
              </Typography>
            </Box>
          ) : null}
          <Box mt={1}>
            <Typography
              component="div"
              variant="body2"
              color={'text.secondary'}
            >
              {description}
            </Typography>
          </Box>
        </Wrapper>
        {!loggedIn ? (
          <Box>
            <Button variant="outlined" onClick={handleSelectOnRegister}>
              {i18n.formatMessage({ id: 'select' })}
            </Button>
          </Box>
        ) : null}
      </ItemTextWrapper>
    </ItemViewWrapper>
  );
}
PackageItemSmallCard.displayName = 'PackageItem(SmallCard)';
