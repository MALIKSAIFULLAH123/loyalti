import { useGlobal } from '@metafox/framework';
import { FriendItemProps } from '@metafox/friend/types';
import { useBlock } from '@metafox/layout';
import {
  ButtonList,
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
import { Box, IconButton, Tooltip, Typography } from '@mui/material';
import * as React from 'react';

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
  const { ItemActionMenu, dispatch, useSession, i18n, useIsMobile } =
    useGlobal();
  const { user: authUser } = useSession();
  const isMobile = useIsMobile(true);
  const { itemProps: { media } = {} } = useBlock();

  if (!item) return null;

  const isAuthUser = authUser?.id === item.id;
  const { statistic, link: to, profile_settings } = item;
  const can_message = item?.extra?.can_message;

  const handleOpenChatRoom = () => {
    dispatch({
      type: 'chat/room/openChatRoom',
      payload: {
        identity: item._identity,
        isMobile
      }
    });
  };

  const actionButton = !isAuthUser ? (
    <ButtonList>
      {can_message ? (
        <Tooltip title={i18n.formatMessage({ id: 'message' })}>
          <IconButton
            aria-label="message"
            size="medium"
            color="primary"
            variant="outlined-square"
            data-testid="itemActionMessage"
            onClick={handleOpenChatRoom}
          >
            <LineIcon icon={'ico-comment-o'} />
          </IconButton>
        </Tooltip>
      ) : null}
      <ItemActionMenu
        identity={identity}
        state={state}
        handleAction={handleAction}
        size="medium"
        color="primary"
        variant="outlined-square"
        icon={'ico-dottedmore-o'}
        tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
      />
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
        <UserAvatar user={item} size={Number.parseInt(media?.width) || 80} />
      </ItemMedia>
      <ItemText>
        <Box pr={item.is_featured ? 2 : 0} sx={{ display: 'flex' }}>
          <UserName
            to={to}
            user={item}
            color={'inherit'}
            InnerWrapAs={ItemTitle}
            sx={{
              display: 'inline-flex',
              maxWidth: '100%',
              wordBreak: 'break-word'
            }}
            showFeaturedBadge
          />
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
