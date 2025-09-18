/**
 * @type: itemView
 * name: group.itemView.reportCard
 * chunkName: group
 */

import { Link, useGlobal } from '@metafox/framework';
import { FriendRequestItemProps } from '@metafox/friend/types';
import {
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  UserAvatar
} from '@metafox/ui';
import { Box, styled, Typography } from '@mui/material';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

const Root = styled(ItemView, { slot: 'root', name: 'FriendItem' })(
  ({ theme }) => ({
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center'
  })
);
const ItemContent = styled('div', { slot: 'ItemContent', name: 'FriendItem' })(
  ({ theme }) => ({
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center'
  })
);

export default function FriendItem({
  identity,
  item,
  wrapProps,
  wrapAs,
  actions
}: FriendRequestItemProps) {
  const { useGetItem } = useGlobal();

  const reportItem = useGetItem(identity);
  const userReport = useGetItem(reportItem?.user);

  if (!reportItem) return null;

  return (
    <Root
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${reportItem.resource_name}`}
      data-eid={identity}
    >
      <ItemContent>
        <ItemMedia>
          <UserAvatar user={userReport} size={48} />
        </ItemMedia>
        <ItemText>
          <ItemTitle>
            <Link
              to={userReport.link}
              children={userReport.full_name}
              color={'inherit'}
            />
          </ItemTitle>

          <ItemSummary>
            <Typography component="div">
              <Box fontWeight="fontWeightMedium" display="inline">
                {reportItem?.reason?.name}
              </Box>
              {reportItem?.feedback && `: ${reportItem?.feedback}`}
            </Typography>
          </ItemSummary>
        </ItemText>
      </ItemContent>
    </Root>
  );
}

FriendItem.LoadingSkeleton = LoadingSkeleton;
FriendItem.displayName = 'FriendItemSmallCard';
