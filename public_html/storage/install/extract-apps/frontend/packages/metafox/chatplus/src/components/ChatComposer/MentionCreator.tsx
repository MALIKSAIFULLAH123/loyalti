/**
 * @type: ui
 * name: statusComposerChat.plugin.mention
 * lazy: false
 */
import { useGlobal } from '@metafox/framework';
import React from 'react';
import qs from 'query-string';
import { ChatplusConfig } from '@metafox/chatplus/types';
import { useRoomPermission } from '@metafox/chatplus/hooks';
import { createStringMatcher } from '@metafox/chatplus/utils';
import { menuStyles } from '@metafox/chatplus/constants';
import { MentionsPlugin, MentionSuggestionEntry } from '../Wrapper';

export default function MentionPlugin(plugins, components, rid, room) {
  const prev_plugins = plugins.findIndex(
    x => x?.()?.props?.newKey === 'mention'
  );

  if (prev_plugins !== -1) {
    plugins.splice(prev_plugins, 1);
  }

  if (room?.t !== 'd') {
    plugins.push(value =>
      AsMention({
        components,
        rid,
        room,
        key: `mention${rid}`,
        newKey: 'mention',
        ...value
      })
    );
  }
}

function AsMention(props) {
  return <Suggestion {...props} As={MentionsPlugin} />;
}

const suggestionsInit = {};

function Suggestion({ As, rid, refContainer }) {
  const { dispatch, getSetting, i18n } = useGlobal();
  const perms = useRoomPermission(rid);

  const setting = getSetting<ChatplusConfig>('chatplus');
  const serverChat = setting?.server?.replace(/\/$/, '');

  const getImageSrc = (item: any) => {
    const params = {
      etag: item?.avatarETag
    };

    return `${serverChat}/avatar/${item?.username}?${qs.stringify(params)}`;
  };

  const onSuccess = (data: { users: any[] }) => {
    if (data.users && data.users.length) {
      const result = data.users.map((user: { username: any; name: any }) => {
        return {
          avatar: getImageSrc(user),
          name: user.name,
          label: user.name,
          user_name: user.username
        };
      });
      const addOn = [];

      if (perms['mention-here']) {
        addOn.push({
          name: 'all',
          user_name: 'all',
          label: i18n.formatMessage({ id: 'notify_all_in_this_room' }),
          isNotify: true
        });
      }

      if (perms['mention-all']) {
        addOn.push({
          name: 'here',
          user_name: 'here',
          label: i18n.formatMessage({ id: 'notify_active_in_this_room' }),
          isNotify: true
        });
      }

      suggestionsInit[rid] = [...result, ...addOn];
    }
  };

  React.useEffect(() => {
    if (rid) {
      dispatch({
        type: 'chatplus/room/presentMembers',
        payload: {
          rid
        },
        meta: { onSuccess }
      });
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rid]);

  const onSearchChange = (e: { value: string }, cb) => {
    if (e.value) {
      const match = createStringMatcher(e.value);

      if (match('all') || match('here')) {
        const result = (suggestionsInit?.[rid] || []).filter(
          item => item.isNotify
        );
        cb(result);

        return;
      }

      const result = (suggestionsInit?.[rid] || []).filter(
        item => match(item.user_name) || match(item.name)
      );
      cb(result);
    } else {
      cb(suggestionsInit?.[rid] || []);
    }
  };

  return (
    <As
      onSearchChange={onSearchChange}
      entryComponent={MentionSuggestionEntry}
      initData={suggestionsInit?.[rid]}
      rid={rid}
      sx={menuStyles}
    />
  );
}
