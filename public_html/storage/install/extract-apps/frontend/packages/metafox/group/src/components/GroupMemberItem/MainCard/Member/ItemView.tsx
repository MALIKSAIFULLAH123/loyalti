import { Link, useGlobal, useSession } from '@metafox/framework';
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
  FeaturedIcon
} from '@metafox/ui';
import { UserItemProps } from '@metafox/user/types';
import { IconButton, Typography, Box } from '@mui/material';
import * as React from 'react';

export default function GroupMemberItem({
  item,
  user,
  identity,
  state,
  handleAction,
  actions,
  itemProps,
  wrapAs,
  wrapProps
}: UserItemProps) {
  const { ItemActionMenu, i18n, dispatch, useIsMobile } = useGlobal();
  const { user: authUser } = useSession();
  const isMobile = useIsMobile(true);

  if (!user) return null;

  const {
    statistic,
    full_name,
    user_name,
    city_location,
    joined,
    extra,
    is_featured
  } = user;
  const to = `/${user_name}`;

  const handleOpenChatRoom = () => {
    dispatch({
      type: 'chat/room/openChatRoom',
      payload: {
        identity: user._identity,
        isMobile
      }
    });
  };

  return (
    <ItemView
      testid={'gropMemberItem'}
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      data-testid={`itemview ${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia>
        <UserAvatar user={user} size={80} />
      </ItemMedia>
      <ItemText>
        <Box sx={{ display: 'flex', alignItems: 'baseline' }}>
          <ItemTitle>
            <Link
              to={to}
              children={full_name}
              color={'inherit'}
              hoverCard={`/user/${user.id}`}
            />
          </ItemTitle>
          <FeaturedIcon icon="ico-check-circle" value={is_featured} />
        </Box>
        <ItemSummary>
          {user?.profile_settings?.profile_view_profile &&
          statistic?.total_mutual &&
          authUser?.id !== user.id ? (
            <div role="button" onClick={actions.presentMutualFriends}>
              <Statistic values={statistic} display="total_mutual" />
            </div>
          ) : (
            <Typography variant="body2">
              {city_location ||
                i18n.formatMessage(
                  {
                    id: 'joined_at'
                  },
                  {
                    joined_date: () => (
                      <FormatDate
                        data-testid="publishedDate"
                        value={joined}
                        format="LL"
                      />
                    )
                  }
                )}
            </Typography>
          )}
        </ItemSummary>
      </ItemText>
      <ButtonList>
        {extra.can_message ? (
          <IconButton
            data-testid="itemActionMessage"
            variant="outlined-square"
            size="medium"
            color="primary"
            onClick={handleOpenChatRoom}
          >
            <LineIcon icon={'ico-comment-o'} />
          </IconButton>
        ) : null}
        {itemProps.showActionMenu ? (
          <ItemActionMenu
            autoHide
            identity={identity}
            state={state}
            handleAction={handleAction}
            size="medium"
            variant="outlined-square"
            color="primary"
            icon="ico-dottedmore-o"
            tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
          />
        ) : null}
      </ButtonList>
    </ItemView>
  );
}
