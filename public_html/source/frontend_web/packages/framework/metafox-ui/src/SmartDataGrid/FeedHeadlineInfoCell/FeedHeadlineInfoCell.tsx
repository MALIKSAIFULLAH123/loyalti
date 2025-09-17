/**
 * @type: ui
 * name: dataGrid.cell.FeedHeadlineInfoCell
 */

import { useGlobal } from '@metafox/framework';
import { Box, styled } from '@mui/material';
import { get, isEmpty, isString } from 'lodash';
import React from 'react';
import HtmlViewer from '@metafox/html-viewer';
import transformHTML from './transformHTML';
import TaggedFriends from './TaggedFriends';

const StyledBox = styled('span', {
  name: 'StyledBox'
})<{ truncateLines: number }>(({ truncateLines }) => ({
  height: '100%',
  ...(truncateLines && {
    wordBreak: 'break-word',
    whiteSpace: 'normal',
    display: '-webkit-box',
    overflow: 'hidden',
    WebkitBoxOrient: 'vertical',
    WebkitLineClamp: `${truncateLines}`
  })
}));

const HeadlineSpanStyled = styled('span')(({ theme }) => ({
  color: theme.palette.text.secondary,
  '& > *:not(.withoutDot):last-child::after': {
    content: "'.'",
    display: 'inline-block'
  }
}));

const HeadlineSpan = styled('span', { name: 'HeadlineSpan' })(({ theme }) => ({
  color: theme.palette.text.secondary
}));

export function ProfileLink({ user, className }: any) {
  if (!user) return null;

  return (
    <Box component="span" className={className}>
      {user?.full_name || user?.title}
    </Box>
  );
}

// todo moved this column to base size.
export default function FeedHeadlineInfoCell({
  row,
  colDef: { field, truncateLines }
}) {
  const { i18n, useSession } = useGlobal();
  const { user: authUser } = useSession();

  const info = get(row, field, null);
  const sx = get(row, 'sx');
  const sxProps = get(sx, field);

  let parent_user = get(row, 'parent_user', null);
  const owner = get(row, 'owner', null);
  const user = get(row, 'user', null);

  if (parent_user === undefined) {
    if (owner?.id !== user?.id) {
      parent_user = owner;
    }
  }

  const tagged_friends = get(row, 'tagged_friends', null);
  const embed_object = get(row, 'embed_object', null);
  const relationWithUser = get(row, 'embed_object.relation_with', null);

  const isParentProfile = 0;
  const isAuthParent = Number(parent_user?.id) === Number(authUser?.id);
  const isOwner = Number(user?.id) === Number(authUser?.id);

  const values = {
    appName: 'feed',
    friend_add: () => <ProfileLink user={embed_object} />,
    profile: () => (
      <HeadlineSpan>
        <ProfileLink user={parent_user} />
      </HeadlineSpan>
    ),
    relation_with: () => <ProfileLink user={relationWithUser} />,
    album_link: () => <Box component="span">{embed_object?.name}</Box>,
    isOwnerTagged: row?.is_owner_tagged ? 1 : 0,
    isAuthUser: 0,
    isAuthParent: isAuthParent ? 1 : 0,
    isCreator: 0,
    isOwner: isOwner ? 1 : 0,
    fromResource: row?.from_resource || 'feed',
    parentType: parent_user?.module_name || 0,
    currentGender: embed_object?.gender,
    isParentProfile: isParentProfile ? 1 : 0,
    isStreaming: embed_object?.is_streaming ? 1 : 0,
    hasRelationWith:
      embed_object?.resource_name === 'user_relation_history' &&
      !!embed_object?.relation_with
        ? 1
        : 0,
    isShared: 0
  };

  const infoHeadline =
    isString(info) && info
      ? i18n.formatMessage(
          {
            id: info
          },
          values
        )
      : info;

  const caretParent = false;

  const tagFriendAndLocation = i18n.formatMessage(
    {
      id: 'feed_tagged_friends_and_location'
    },
    {
      hasInfo: isEmpty(infoHeadline) ? 1 : 0,
      hasTagFriend:
        tagged_friends?.length && !row?.is_hide_tagged_headline ? 1 : 0,
      hasLocation: row?.location && row?.is_show_location ? 1 : 0,
      tag_friend: () => (
        <HeadlineSpan>
          <TaggedFriends
            item_type={row?.item_type || row?.resource_name}
            item_id={row.item_id || row?.id}
            total={row?.total_friends_tagged}
            users={tagged_friends}
          />
        </HeadlineSpan>
      ),
      location: () => (
        <HeadlineSpan>
          {i18n.formatMessage(
            {
              id: 'at_tagged_place'
            },
            {
              name: row?.location?.address,
              bold: () => <Box component="span">{row?.location?.address}</Box>
            }
          )}
        </HeadlineSpan>
      )
    }
  );

  if (
    embed_object?.error ||
    (!tagged_friends?.length &&
      !row?.location &&
      ((isString(infoHeadline) && isEmpty(infoHeadline?.trim())) ||
        !infoHeadline))
  )
    return null;

  return (
    <StyledBox truncateLines={truncateLines} sx={sxProps}>
      <HeadlineSpan>
        <ProfileLink user={user} />{' '}
      </HeadlineSpan>
      <HeadlineSpanStyled>
        {infoHeadline ? (
          <HeadlineSpan className={caretParent ? 'withoutDot' : ''}>
            {isString(infoHeadline) ? (
              <HtmlViewer html={infoHeadline} transform={transformHTML} />
            ) : (
              infoHeadline
            )}
          </HeadlineSpan>
        ) : null}
        {tagged_friends?.length || row?.location ? tagFriendAndLocation : null}
      </HeadlineSpanStyled>
    </StyledBox>
  );
}
