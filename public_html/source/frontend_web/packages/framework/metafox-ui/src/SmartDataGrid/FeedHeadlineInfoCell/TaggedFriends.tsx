import { useGlobal } from '@metafox/framework';
import { Box } from '@mui/material';
import * as React from 'react';
import { ProfileLink } from './FeedHeadlineInfoCell';

interface IProps {
  users: any;
  className?: any;
  total?: any;
  item_type?: any;
  item_id?: any;
}

export default function TaggedFriends({
  users,
  className,
  total,
  item_type,
  item_id
}: IProps) {
  const { i18n } = useGlobal();

  if (1 === total)
    return (
      <span>
        {i18n.formatMessage(
          {
            id: 'with_tagged_friend'
          },
          {
            user0: () => <ProfileLink user={users[0]} className={className} />,
            value: total - 1
          }
        )}
      </span>
    );

  if (2 === total) {
    return (
      <span>
        {i18n.formatMessage(
          {
            id: 'with_tagged_friend_and_user'
          },
          {
            user0: () => <ProfileLink user={users[0]} className={className} />,
            user1: () => <ProfileLink user={users[1]} className={className} />
          }
        )}
      </span>
    );
  }

  return (
    <span>
      {i18n.formatMessage(
        {
          id: 'with_tagged_friend_and_others'
        },
        {
          user0: () => <ProfileLink user={users[0]} className={className} />,
          others: str => <Box component="span">{str}</Box>,
          value: total - 1
        }
      )}
    </span>
  );
}
