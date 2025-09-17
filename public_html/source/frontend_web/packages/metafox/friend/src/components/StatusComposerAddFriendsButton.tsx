/**
 * @type: ui
 * name: statusComposer.control.StatusTagsFriendButton
 * chunkName: statusComposerControl
 */

import {
  StatusComposerControlProps,
  useGetItem,
  useGlobal
} from '@metafox/framework';
import { get } from 'lodash';
import React from 'react';

export default function TagFriendToStatusComposer({
  control: Control,
  composerRef,
  disabled,
  parentIdentity,
  parentType,
  userIdentity
}: StatusComposerControlProps) {
  const { i18n, dispatch } = useGlobal();
  const parentItem = useGetItem(parentIdentity);
  const tagType =
    parentType === 'group' && parentItem?.reg_method ? 'member' : 'other';

  const handlePickedValue = value => {
    const { setTags } = composerRef.current;

    setTags('friends', {
      as: 'StatusComposerControlTaggedFriends',
      priority: 1,
      value
    });
  };

  const handleClick = () => {
    dispatch({
      type: 'friend/friendPicker',
      payload: {
        forceUpdateValueOnClose: true,
        users: get(composerRef.current.state, 'tags.friends.value'),
        parentIdentity,
        parentType,
        userIdentity
      },
      meta: { onSuccess: handlePickedValue }
    });
  };

  return (
    <Control
      icon="ico-user1-plus-o"
      testid="statusTagFriendsButton"
      disabled={disabled}
      title={i18n.formatMessage(
        {
          id: disabled ? 'this_cant_be_combined' : 'tag_feed_type'
        },
        { tagType }
      )}
      label={i18n.formatMessage({ id: 'tag_feed_type' }, { tagType })}
      onClick={handleClick}
    />
  );
}
