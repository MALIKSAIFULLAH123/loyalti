import { FriendItemProps } from '@metafox/friend/types';
import { useBlock } from '@metafox/layout';
import {
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  UserAvatar,
  UserName
} from '@metafox/ui';
import { Box, Typography } from '@mui/material';
import moment from 'moment';
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
  const { itemProps: { media } = {} } = useBlock();

  if (!item) return null;

  const { link: to, birthday, age, birthday_format } = item;

  const day_phrase = moment(birthday, birthday_format).format(
    age ? 'LL' : 'MMMM D'
  );

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia>
        <UserAvatar user={item} size={Number.parseInt(media?.width)} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <UserName to={to} user={item} color={'inherit'} />
        </ItemTitle>
        <Box>
          {birthday && (
            <ItemSummary>
              <Typography variant="body2" color="text.secondary">
                {day_phrase}
              </Typography>
            </ItemSummary>
          )}
        </Box>
      </ItemText>
      <Box>
        <Typography variant="body2" color="text.secondary">
          {item?.new_age_phrase}
        </Typography>
      </Box>
    </ItemView>
  );
}

FriendItem.displayName = 'FriendItemViewMainCard';
