/* eslint-disable react-hooks/rules-of-hooks */
/* eslint-disable @typescript-eslint/no-unused-vars */
import { Link, useGetItem, useGlobal } from '@metafox/framework';
import { GroupItemProps } from '@metafox/group';
import {
  ItemMedia,
  ItemText,
  ItemView,
  UserAvatar,
  ItemTitle,
  ItemSummary,
  LineIcon,
  ButtonList,
  FeaturedIcon,
  Statistic,
  FormatDate
} from '@metafox/ui';
import { IconButton, Typography } from '@mui/material';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

export default function UserItem({
  item,
  identity,
  actions,
  wrapAs,
  wrapProps
}: GroupItemProps) {
  const { i18n, useSession } = useGlobal();
  const user = useGetItem(item?.user);
  const { user: authUser } = useSession();

  if (!user || !item) return null;

  const {
    full_name,
    user_name,
    is_featured,
    statistic,
    city_location,
    joined
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
        {item.answers.length ? (
          <ItemSummary>
            <div role="button" onClick={actions.viewMemberQuestions}>
              {i18n.formatMessage({ id: 'view_membership_questions' })}
            </div>
          </ItemSummary>
        ) : (
          <ItemSummary>
            {statistic?.total_mutual && authUser?.id !== user.id ? (
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
        )}
      </ItemText>
      <ButtonList>
        <IconButton
          variant="outlined-square"
          size="medium"
          color="primary"
          onClick={actions.declinePendingRequest}
        >
          <LineIcon icon={'ico-close'} />
        </IconButton>
        <IconButton
          variant="outlined-square"
          size="medium"
          color="primary"
          onClick={actions.approvePendingRequest}
        >
          <LineIcon icon={'ico-check'} />
        </IconButton>
      </ButtonList>
    </ItemView>
  );
}

UserItem.LoadingSkeleton = LoadingSkeleton;
UserItem.displayName = 'PendingUserMainCard';
