import { Link, useGlobal } from '@metafox/framework';
import { FriendRequestItemProps } from '@metafox/friend/types';
import { UserAvatar } from '@metafox/ui';
import * as React from 'react';
import useStyles from './FriendItemView.styles';
import LoadingSkeleton from './LoadingSkeleton';

const FriendItemSmallView = ({ item }: FriendRequestItemProps) => {
  const classes = useStyles();
  const { statistic } = item;
  const { i18n } = useGlobal();
  const to = `/${item.user_name}`;

  return (
    <div className={classes.root}>
      <div className={classes.itemOuter}>
        <div className={classes.itemSmallInner}>
          <div className={classes.itemSmallMedia}>
            <div className={classes.imgSmallWrapper}>
              <UserAvatar user={item} size={48} />
            </div>
          </div>
          <div className={classes.userSmallInfo}>
            <div className={classes.userSmallTitle}>
              <Link to={to} children={item.full_name} color={'inherit'} />
            </div>
            <div className={classes.friendSmallInfo} role="button">
              <span className={classes.mutualFriend}>
                {statistic?.total_mutual}
              </span>
              {i18n.formatMessage(
                { id: 'total_mutual_friend' },
                { value: statistic?.total_mutual }
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

FriendItemSmallView.LoadingSkeleton = LoadingSkeleton;

export default FriendItemSmallView;
