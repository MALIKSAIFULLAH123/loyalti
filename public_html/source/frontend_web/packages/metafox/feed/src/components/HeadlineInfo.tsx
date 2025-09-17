import { useGlobal, Link } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { styled } from '@mui/material';
import { isEmpty, isString } from 'lodash';
import * as React from 'react';
import HeadlineSpan from './HeadlineSpan';
import ProfileLink from './FeedItemView/ProfileLink';
import useComposerContext from '../hooks/useComposerContext';
import TaggedFriends from './FeedItemView/FeedItemTaggedFriends';
import TaggedPlace from './FeedItemView/FeedItemTaggedPlace';
import { LineIcon } from '@metafox/ui';

export const AvatarWrapper = styled('div', { name: 'AvatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1.5)
  })
);

const HeadlineSpanStyled = styled('span')(({ theme }) => ({
  color: theme.palette.text.secondary,
  '& > *:not(.withoutDot):last-child::after': {
    content: "'.'",
    display: 'inline-block'
  }
}));

export const HeadlineInfo = ({
  isEmbed = false,
  info,
  item,
  embed_object,
  classes,
  tagged_friends,
  location,
  is_show_location = true,
  item_type,
  item_id,
  total_friends_tagged
}) => {
  const { i18n, usePageParams, useGetItem, useSession } = useGlobal();
  const composerContext = useComposerContext();

  const pageParams = usePageParams();
  const { user: authUser } = useSession();

  let parent_user = useGetItem(item?.parent_user);
  const owner = useGetItem(item?.owner);
  const user = useGetItem(item?.user);

  if (parent_user === undefined) {
    if (owner?.id !== user?.id) {
      parent_user = owner;
    }
  }

  const relationWithUser = useGetItem(embed_object?.relation_with);
  const isOwner = Number(user?.id) === Number(authUser?.id);

  const isAuthUser = Number(pageParams?.profile_id) === Number(authUser?.id);
  const isAuthParent = Number(parent_user?.id) === Number(authUser?.id);
  const isCreator = Number(user?.id) === Number(pageParams?.profile_id);
  const isParentProfile =
    pageParams?.profile_id &&
    Number(pageParams?.profile_id) === Number(parent_user?.id);

  const compactParams = Object.assign(
    {},
    pageParams,
    composerContext?.pageParams
  );

  const values = {
    ...compactParams,
    friend_add: () => (
      <ProfileLink user={embed_object} className={classes.profileLink} />
    ),
    profile: () => (
      <HeadlineSpan>
        <ProfileLink user={parent_user} className={classes.profileLink} />
      </HeadlineSpan>
    ),
    relation_with: () => (
      <ProfileLink user={relationWithUser} className={classes.profileLink} />
    ),
    album_link: () => <Link to={embed_object?.link}>{embed_object?.name}</Link>,
    isOwnerTagged: item?.is_owner_tagged ? 1 : 0,
    isAuthUser: isAuthUser ? 1 : 0,
    isAuthParent: isAuthParent ? 1 : 0,
    isCreator: isCreator ? 1 : 0,
    isOwner: isOwner ? 1 : 0,
    fromResource: item?.from_resource,
    parentType: parent_user?.module_name || 0,
    currentGender: embed_object?.gender,
    isParentProfile: isParentProfile ? 1 : 0,
    isStreaming: embed_object?.is_streaming ? 1 : 0,
    hasRelationWith:
      embed_object?.resource_name === 'user_relation_history' &&
      !!embed_object?.relation_with
        ? 1
        : 0,
    isShared: isEmbed ? 1 : 0
  };

  let infoHeadline =
    isString(info) && info
      ? i18n.formatMessage(
          {
            id: info
          },
          values
        )
      : info;

  const caretParent = isEmbed && parent_user;

  if (caretParent) {
    infoHeadline = i18n.formatMessage(
      {
        id: 'to_parent_user'
      },
      {
        icon: () => (
          <LineIcon icon="ico-caret-right" className={classes.caretIcon} />
        ),
        parent_user: () => (
          <ProfileLink user={parent_user} className={classes.profileLink} />
        )
      }
    );
  }

  const tagFriendAndLocation = i18n.formatMessage(
    {
      id: 'feed_tagged_friends_and_location'
    },
    {
      hasInfo: isEmpty(infoHeadline) ? 1 : 0,
      hasTagFriend:
        tagged_friends?.length && !item?.is_hide_tagged_headline ? 1 : 0,
      hasLocation: location && is_show_location ? 1 : 0,
      tag_friend: () => (
        <HeadlineSpan>
          <TaggedFriends
            item_type={item_type}
            item_id={item_id}
            total={total_friends_tagged}
            users={tagged_friends}
            className={classes.profileLink}
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
              name: location.address,
              bold: () => (
                <TaggedPlace
                  place={location}
                  className={
                    isEmbed ? classes.profileLink : classes.locationLink
                  }
                />
              )
            }
          )}
        </HeadlineSpan>
      )
    }
  );

  if (
    embed_object?.error ||
    (!infoHeadline && !tagged_friends?.length && !location)
  )
    return null;

  return (
    <HeadlineSpanStyled>
      {infoHeadline ? (
        <HeadlineSpan className={caretParent ? 'withoutDot' : ''}>
          {isString(infoHeadline) ? (
            <HtmlViewer html={infoHeadline} />
          ) : (
            infoHeadline
          )}
        </HeadlineSpan>
      ) : null}
      {tagged_friends?.length || location ? tagFriendAndLocation : null}
    </HeadlineSpanStyled>
  );
};

export default HeadlineInfo;
