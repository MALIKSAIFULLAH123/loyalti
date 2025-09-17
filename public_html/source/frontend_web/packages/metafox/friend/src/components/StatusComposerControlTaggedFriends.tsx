/**
 * @type: ui
 * name: StatusComposerControlTaggedFriends
 */
import { StatusComposerControlProps, useGlobal } from '@metafox/framework';
import React from 'react';

type Props = StatusComposerControlProps;

function ProfileLink({ children, onClick, allowTagFriends }) {
  return (
    <b
      children={children}
      onClick={onClick}
      style={{ cursor: allowTagFriends ? 'pointer' : 'auto' }}
    />
  );
}

export default function StatusComposerControlTaggedFriends(props: Props) {
  const { i18n, dispatch, getSetting } = useGlobal();
  const { value: users, parentType, parentIdentity, userIdentity } = props;

  const allowTagFriends = getSetting('activity.feed.enable_tag_friends');

  if (!users?.length) return null;

  const total = users.length;

  const handlePickedValue = value => {
    const { setTags } = props.composerRef.current;

    setTags('friends', {
      as: 'StatusComposerControlTaggedFriends',
      priority: 1,
      value
    });
  };

  const handleClick = () => {
    if (!allowTagFriends) return;

    dispatch({
      type: 'friend/friendPicker',
      payload: {
        forceUpdateValueOnClose: true,
        users,
        parentIdentity,
        parentType,
        userIdentity
      },
      meta: { onSuccess: handlePickedValue }
    });
  };

  if (1 === total)
    return (
      <span>
        {i18n.formatMessage(
          {
            id: 'with_tagged_friend'
          },
          {
            user0: () => (
              <ProfileLink
                onClick={handleClick}
                children={users[0].full_name}
                allowTagFriends={allowTagFriends}
              />
            )
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
            user0: () => (
              <ProfileLink
                onClick={handleClick}
                children={users[0].full_name}
                allowTagFriends={allowTagFriends}
              />
            ),
            user1: () => (
              <ProfileLink
                onClick={handleClick}
                children={users[1].full_name}
                allowTagFriends={allowTagFriends}
              />
            )
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
          user0: () => (
            <ProfileLink
              children={users[0].full_name}
              onClick={handleClick}
              allowTagFriends={allowTagFriends}
            />
          ),
          others: str => (
            <ProfileLink
              children={str}
              onClick={handleClick}
              allowTagFriends={allowTagFriends}
            />
          ),
          value: total - 1
        }
      )}
    </span>
  );
}
