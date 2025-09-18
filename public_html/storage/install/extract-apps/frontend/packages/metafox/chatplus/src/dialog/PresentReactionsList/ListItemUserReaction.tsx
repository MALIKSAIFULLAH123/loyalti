import MsgAvatar from '@metafox/chatplus/components/Messages/MsgAvatar';
import { useSessionUser } from '@metafox/chatplus/hooks';
import { Link, useGlobal } from '@metafox/framework';
import { Button } from '@mui/material';
import React from 'react';
import useStyles from './stylesListItem';

const ListItemUserReaction = ({ data, unsetReaction }: any) => {
  const classes = useStyles();
  const { useDialog, i18n } = useGlobal();
  const { closeDialog } = useDialog();

  const userSession = useSessionUser();

  const me = userSession.username;

  if (!data) return null;

  return data.usernames.map((user, key) => (
    <div className={classes.root} key={key}>
      <div className={classes.itemOuter}>
        <div className={classes.itemSmallInner}>
          <div className={classes.itemMainContent}>
            <div className={classes.itemSmallMedia}>
              <div className={classes.imgSmallWrapper}>
                <MsgAvatar
                  username={user.username}
                  name={user.name}
                  size={40}
                  avatarETag={new Date().toString()}
                />
              </div>
              <div className={classes.itemReactSmall}>
                <img
                  className={classes.imgSmallReactionIcon}
                  src={user.icon}
                  alt="reaction"
                />
              </div>
            </div>
            <div className={classes.userSmallInfo}>
              <div className={classes.userSmallTitle} onClick={closeDialog}>
                <Link to={`/${user.username}`} color={'inherit'}>{user.name}</Link>
              </div>
              {/* {mutual_friends?.total ? (
                <div className={classes.friendSmallInfo} role="button">
                  <span className={classes.mutualFriend}>
                    {mutual_friends?.total}
                  </span>
                  {i18n.formatMessage(
                    { id: 'total_mutual_friend' },
                    { value: mutual_friends?.total }
                  )}
                </div>
              ) : null} */}
            </div>
          </div>
          {me === user.username && (
            <Button
              className={classes.actionContent}
              color="primary"
              onClick={() => unsetReaction(user.id)}
            >
              {i18n.formatMessage({ id: 'remove' })}
            </Button>
          )}
        </div>
      </div>
    </div>
  ));
};

export default ListItemUserReaction;
