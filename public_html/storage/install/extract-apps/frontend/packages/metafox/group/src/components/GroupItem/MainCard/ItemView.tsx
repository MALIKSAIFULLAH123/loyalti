import { Link, useGlobal } from '@metafox/framework';
import { GroupItemProps } from '@metafox/group/types';
import {
  ButtonList,
  FeaturedFlag,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon,
  SponsorFlag,
  Statistic,
  Image,
  PendingFlag,
  ButtonAction
} from '@metafox/ui';
import { filterShowWhen, withDisabledWhen, getImageSrc } from '@metafox/utils';
import { Box, Typography } from '@mui/material';
import { styled } from '@mui/material/styles';
import { camelCase, isEmpty } from 'lodash';
import * as React from 'react';

const TypographyStyled = styled(Typography)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  fontWeight: '600',
  marginLeft: theme.spacing(1)
}));

const FlagWrapper = styled('div', {
  name: 'GroupMainCardItem',
  slot: 'flagWrapper'
})(({ theme }) => ({
  marginBottom: theme.spacing(1)
}));

const ItemTextWrapper = styled(ItemText, {
  name: 'ItemTextWrapper'
})(({ theme }) => ({
  width: '100%'
}));

const ItemMediaWrapper = styled(ItemMedia, {
  name: 'ItemMediaWrapper',
  shouldForwardProp: props => props !== 'position' && props !== 'isCover'
})<{ position: number; isCover?: boolean }>(({ theme, position, isCover }) => ({
  '& img': {
    border: 'none',
    height: '100%',
    ...(isCover && {
      objectPosition: 'top'
    }),
    ...(position && {
      objectPosition: 'top',
      transform: `translateY(${position})`,
      height: 'auto !important',
      minHeight: '100%'
    })
  }
}));

const RegName = styled(Box, { slot: 'IconButtonWrapper' })(({ theme }) => ({
  color: theme.palette.text.secondary,
  fontSize: 13
}));

const Summary = styled(Box, { slot: 'Summary' })(({ theme }) => ({
  display: 'flex',
  '&>div:not(:last-child):after': {
    content: '"·"',
    paddingLeft: '0.25em',
    paddingRight: '0.25em'
  }
}));

const LinkStyled = styled(Link, { name: 'Link' })(({ theme }) => ({
  whiteSpace: 'nowrap',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  display: 'block'
}));
// support only new version, save %
const getTopPosition = x => (`${x}`.includes('%') ? x : undefined);

export default function GroupMainCardItem({
  item,
  itemActionMenu,
  identity,
  handleAction,
  user,
  itemProps,
  wrapProps,
  wrapAs
}: GroupItemProps) {
  const { ItemActionMenu, useSession, i18n, getAcl, getSetting, assetUrl } =
    useGlobal();
  const { loggedIn, user: authUser } = useSession();

  if (!item) return null;

  const {
    title,
    id,
    statistic,
    link = '',
    extra,
    cover_photo_position = 0
  } = item || {};
  const to = link || `/group/${id}`;

  const acl = getAcl();
  const setting = getSetting();
  const condition = {
    item,
    acl,
    setting,
    isAuth: authUser?.id === user?.id
  };

  const actionMenuItems = withDisabledWhen(
    filterShowWhen(itemActionMenu, condition),
    condition
  );

  const reactButton: any = actionMenuItems.splice(0, 1)[0];

  const cover = getImageSrc(
    item.cover,
    '500',
    assetUrl('group.cover_no_image')
  );

  const onAction = onSuccess => {
    handleAction(reactButton.value, { onSuccess });
  };

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      mediaPlacement="top"
      identity={identity}
    >
      <ItemMediaWrapper
        isCover={!isEmpty(item?.cover)}
        position={getTopPosition(cover_photo_position)}
      >
        <Link to={to} identityTracking={identity}>
          <Image src={cover} aspectRatio={'165'} />
        </Link>
      </ItemMediaWrapper>
      <ItemTextWrapper>
        <ItemTitle>
          {item.is_featured || item.is_sponsor || item.is_pending ? (
            <FlagWrapper>
              <FeaturedFlag value={item.is_featured} variant="itemView" />
              <SponsorFlag
                value={item.is_sponsor}
                variant="itemView"
                item={item}
              />
              <PendingFlag variant="itemView" value={item.is_pending} />
            </FlagWrapper>
          ) : null}
          <LinkStyled
            to={to}
            color={'inherit'}
            hoverCard={`/group/${id}`}
            identityTracking={identity}
          >
            {title}
          </LinkStyled>
        </ItemTitle>
        <Summary>
          {extra?.can_view_privacy ? <RegName>{item.reg_name}</RegName> : null}
          <Statistic
            values={statistic}
            display={'total_member'}
            skipZero
            truthyValue
          />
        </Summary>
        <Box sx={{ mt: 'auto', pt: 2 }}>
          {loggedIn ? (
            <ButtonList>
              {reactButton && (
                <Box sx={{ flex: 1, minWidth: 0 }}>
                  <ButtonAction
                    data-testid={camelCase(
                      `Button Action ${reactButton?.label}`
                    )}
                    isIcon
                    size="medium"
                    color="primary"
                    variant="outlined-square"
                    disabled={reactButton?.disabled}
                    action={onAction}
                    sx={{ width: '100%' }}
                    className={reactButton.name}
                  >
                    <LineIcon icon={reactButton?.icon} />
                    <TypographyStyled variant="body1">
                      {i18n.formatMessage({ id: reactButton.label })}
                    </TypographyStyled>
                  </ButtonAction>
                </Box>
              )}
              {item.extra && itemProps.showActionMenu ? (
                <ItemActionMenu
                  identity={identity}
                  items={actionMenuItems}
                  handleAction={handleAction}
                  size="medium"
                  variant="outlined-square"
                  color="primary"
                  icon="ico-dottedmore-o"
                  tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
                  sx={{ ml: 1 }}
                />
              ) : null}
            </ButtonList>
          ) : null}
        </Box>
      </ItemTextWrapper>
    </ItemView>
  );
}
