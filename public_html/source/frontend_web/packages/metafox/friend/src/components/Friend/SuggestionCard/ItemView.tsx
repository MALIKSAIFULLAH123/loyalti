import { useGlobal } from '@metafox/framework';
import {
  APP_FRIEND,
  RESOURCE_FRIEND_SUGGESTION
} from '@metafox/friend/constant';
import { FriendItemProps } from '@metafox/friend/types';
import {
  ButtonList,
  FeaturedIcon,
  FormatDate,
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon,
  Statistic,
  UserAvatar,
  UserName
} from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { Box, Button, Typography, styled } from '@mui/material';
import * as React from 'react';

const UserNameStyled = styled(UserName, { slot: 'userName' })(({ theme }) => ({
  wordBreak: 'break-word'
}));

export default function FriendItem({
  item,
  user,
  identity,
  handleAction,
  state,
  wrapAs,
  wrapProps,
  actions
}: FriendItemProps) {
  const {
    ItemActionMenu,
    useSession,
    i18n,
    useIsMobile,
    useResourceMenu,
    getAcl,
    getSetting
  } = useGlobal();
  const { user: authUser } = useSession();
  const isMobile = useIsMobile(true);
  const session = useSession();
  const acl = getAcl();
  const setting = getSetting();

  const menu = useResourceMenu(
    APP_FRIEND,
    RESOURCE_FRIEND_SUGGESTION,
    'itemActionMenu'
  );

  if (!item) return null;

  const isAuthUser = authUser?.id === item.id;
  const { statistic, link: to, profile_settings } = item;

  const condition = { item, acl, setting, session };
  const memuItems = filterShowWhen(menu.items, condition);
  const actionButtons = memuItems.slice(0, 1);
  const actionMenuItems = memuItems.slice(1);

  const actionButton =
    !isAuthUser && memuItems.length ? (
      <ButtonList>
        {actionButtons.map((btn, index) => (
          <Button
            key={btn.label}
            data-testid={btn?.name}
            variant={btn?.variant || 'outlined'}
            startIcon={btn?.icon && <LineIcon icon={btn.icon} />}
            onClick={() => handleAction(btn.value, { isMobile })}
            color={(btn.color || 'primary') as any}
            size={btn?.size || 'medium'}
          >
            {btn.label}
          </Button>
        ))}
        {actionMenuItems?.length ? (
          <ItemActionMenu
            items={actionMenuItems}
            handleAction={handleAction}
            size="medium"
            color="primary"
            variant="outlined-square"
            icon={'ico-dottedmore-o'}
            tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
          />
        ) : null}
      </ButtonList>
    ) : null;

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia>
        <UserAvatar user={item} size={80} hoverCard={`/user/${item?.id}`} />
      </ItemMedia>
      <ItemText>
        <Box sx={{ display: 'flex', alignItems: 'baseline' }}>
          <ItemTitle>
            <UserNameStyled
              to={to}
              user={item}
              color={'inherit'}
              hoverCard={`/user/${item?.id}`}
            />
          </ItemTitle>
          <FeaturedIcon icon="ico-check-circle" value={item.is_featured} />
        </Box>
        {profile_settings?.profile_view_profile ? (
          <Box>
            {!isAuthUser && statistic.total_mutual > 0 ? (
              <ItemSummary role="button" onClick={actions.showMutualFriends}>
                <Statistic values={statistic} display="total_mutual" />
              </ItemSummary>
            ) : (
              <ItemSummary>
                <Typography variant="body2" color="text.secondary">
                  {item.country_name && item.country_state_name
                    ? `${item.country_state_name}, ${item.country_name}`
                    : i18n.formatMessage(
                        {
                          id: 'joined_at'
                        },
                        {
                          joined_date: () => (
                            <FormatDate
                              data-testid="joinedDate"
                              value={item.joined}
                              format="LL"
                            />
                          )
                        }
                      )}
                </Typography>
              </ItemSummary>
            )}
          </Box>
        ) : null}
        <Box sx={{ display: { xs: 'block', md: 'none' }, pt: 1 }}>
          {actionButton}
        </Box>
      </ItemText>
      <Box sx={{ display: { xs: 'none', md: 'block' } }}>{actionButton}</Box>
    </ItemView>
  );
}

FriendItem.displayName = 'FriendItemViewMainCard';
