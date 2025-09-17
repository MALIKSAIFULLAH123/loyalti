import { useGlobal } from '@metafox/framework';
import {
  ButtonList,
  FormatDate,
  ItemAction,
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  Statistic,
  UserAvatar,
  UserName
} from '@metafox/ui';
import { UserItemProps } from '@metafox/user/types';
import { filterShowWhen } from '@metafox/utils';
import * as React from 'react';
import { Box } from '@mui/material';

export default function UserItem({
  item,
  identity,
  state,
  handleAction,
  actions,
  itemProps,
  wrapAs,
  wrapProps,
  itemActionMenu
}: UserItemProps) {
  const {
    ItemActionMenu,
    i18n,
    useSession,
    getAcl,
    getSetting,
    usePageParams
  } = useGlobal();
  const session = useSession();
  const { user: userAuth } = session;
  const { tab } = usePageParams();
  const acl = getAcl();
  const setting = getSetting();
  const condition = {
    item,
    acl,
    setting,
    session,
    isAuth: userAuth?.id === item?.id
  };

  const actionMenuItems = filterShowWhen(itemActionMenu, condition);

  if (!item) return null;

  const {
    statistic,
    user_name,
    city_location,
    joined,
    last_activity,
    profile_settings
  } = item;
  const to = `/${user_name}`;
  const showLastActivity = tab === 'recent' && last_activity;

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia>
        <UserAvatar user={item} size={80} hoverCard={`/user/${item.id}`} />
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
        {userAuth?.id !== item?.id && profile_settings?.profile_view_profile ? (
          <ItemSummary>
            {showLastActivity ? (
              i18n.formatMessage(
                {
                  id: 'last_activity_on_time'
                },
                {
                  datetime: () => (
                    <FormatDate
                      data-testid="publishedDate"
                      value={last_activity}
                      format="LLL"
                    />
                  )
                }
              )
            ) : statistic?.total_mutual ? (
              <div role="button" onClick={actions.presentMutualFriends}>
                <Statistic values={statistic} display="total_mutual" />
              </div>
            ) : (
              city_location ||
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
              )
            )}
          </ItemSummary>
        ) : null}
      </ItemText>
      <ItemAction>
        {itemProps.showActionMenu ? (
          <ButtonList>
            <ItemActionMenu
              identity={identity}
              state={state}
              items={actionMenuItems}
              handleAction={handleAction}
              size="medium"
              variant="outlined-square"
              color="primary"
              icon="ico-dottedmore-o"
              tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
            />
          </ButtonList>
        ) : null}
      </ItemAction>
    </ItemView>
  );
}

UserItem.displayName = 'UserItemMainCard';
