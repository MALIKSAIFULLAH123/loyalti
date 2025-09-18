/**
 * @type: itemView
 * name: story.itemView.ownerCard
 */

import { Link, useGlobal } from '@metafox/framework';
import { StoryUserProps } from '@metafox/story/types';
import {
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  UserAvatar
} from '@metafox/ui';
import * as React from 'react';

export default function OwnerCardView({ wrapAs, wrapProps }: StoryUserProps) {
  const { useSession } = useGlobal();

  const { user: item } = useSession();

  if (!item) return null;

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="owner-avatar-card">
      <ItemMedia>
        <UserAvatar
          user={item}
          size={48}
          hoverCard={false}
          sx={{ pointerEvents: 'none' }}
          showLiveStream
        />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Link color={'inherit'} underline="none">
            {item?.full_name}
          </Link>
        </ItemTitle>
      </ItemText>
    </ItemView>
  );
}
