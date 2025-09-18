import { Link, useGetItem, useGlobal } from '@metafox/framework';
import { PagesItemProps } from '@metafox/pages';
import {
  ItemMedia,
  ItemText,
  ItemView,
  Statistic,
  UserAvatar,
  ItemTitle,
  ItemSummary,
  FormatDate,
  FeaturedIcon
} from '@metafox/ui';
import { Button } from '@mui/material';
import * as React from 'react';

export default function GroupInvitation({
  item,
  identity,
  state,
  handleAction,
  actions,
  itemProps,
  wrapAs,
  wrapProps
}: PagesItemProps) {
  const { i18n } = useGlobal();
  const user = useGetItem(item?.owner);

  if (!user) return null;

  const {
    statistic,
    full_name,
    user_name,
    city_location,
    joined,
    is_featured
  } = user;
  const to = `/${user_name}`;

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      data-testid={`itemview ${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia>
        <UserAvatar user={user} size={80} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Link
            to={to}
            children={full_name}
            color={'inherit'}
            hoverCard={`/user/${user.id}`}
          />
          <FeaturedIcon icon="ico-check-circle" value={is_featured} />
        </ItemTitle>
        <ItemSummary>
          {user?.profile_settings?.profile_view_profile &&
          statistic?.total_mutual ? (
            <div role="button" onClick={actions?.presentMutualFriends}>
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
      </ItemText>
      <Button
        variant="outlined"
        size="medium"
        color="primary"
        onClick={actions.cancelInvitation}
      >
        {i18n.formatMessage({ id: 'cancel' })}
      </Button>
    </ItemView>
  );
}
