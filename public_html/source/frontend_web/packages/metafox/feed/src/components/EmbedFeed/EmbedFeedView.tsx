import { GlobalState, Link, useGlobal } from '@metafox/framework';
import { FromNow, PrivacyIcon, UserAvatar, UserName } from '@metafox/ui';
import { styled, Box } from '@mui/material';
import clsx from 'clsx';
import { isString } from 'lodash';
import * as React from 'react';
import { useSelector } from 'react-redux';
import { FeedItemViewProps } from '../../types';
import FeedEmbedObjectView from '../EmbedObject';
import FeedStatusView from '../FeedStatus';
import HeadlineInfo from '../HeadlineInfo';
import HeadlineSpan from '../HeadlineSpan';
import useStyles from './styles';
import { getTaggedFriendsPhotoSelector } from '@metafox/core/selectors/status';

const AvatarWrapper = styled('div', { name: 'AvatarWrapper' })(({ theme }) => ({
  marginRight: theme.spacing(1.5)
}));

export default function EmbedFeedInFeed({
  item,
  user,
  parent_user,
  identity,
  isShared
}: FeedItemViewProps) {
  const classes = useStyles();
  const { jsxBackend } = useGlobal();

  const tagged_friends_list = useSelector((state: GlobalState) =>
    getTaggedFriendsPhotoSelector(state, item)
  );

  // ensure item exists.
  if (!item) return null;

  const {
    info,
    embed_object,
    status,
    status_background,
    location,
    is_show_location = true,
    link
  } = item;

  // do not embed feed in feed.
  if (isString(embed_object) && embed_object.startsWith('feed')) return null;

  if (!item || !user) return null;

  let noMarginBottom = false;

  const media =
    isString(embed_object) &&
    ['photo', 'video'].includes(embed_object?.split('.')[0]);

  if (media || status_background) {
    noMarginBottom = true;
  }

  const showMapview = !!location?.show_map && !embed_object;

  return (
    <div
      className={clsx(classes.root, noMarginBottom && classes.noMarginBottom)}
    >
      {showMapview ? (
        <Box>
          {jsxBackend.render({
            component: 'ui.mapDisplay',
            props: { location }
          })}
        </Box>
      ) : null}
      <FeedEmbedObjectView embed={embed_object} feed={item} />
      <div className={clsx(classes.header, classes.paddingTopMedia)}>
        <AvatarWrapper>
          <UserAvatar user={user} size={48} />
        </AvatarWrapper>
        <div className={classes.headerInfo}>
          <div className={classes.headerHeadline}>
            <HeadlineSpan>
              <UserName
                to={user.user_name || user?.link}
                user={user}
                className={classes.profileLink}
              />{' '}
            </HeadlineSpan>
            <HeadlineInfo
              isEmbed
              info={info}
              embed_object={embed_object}
              classes={classes}
              item={item}
              tagged_friends={tagged_friends_list}
              location={location}
              is_show_location={is_show_location}
              item_type={item.item_type}
              item_id={item.item_id}
              total_friends_tagged={item.total_friends_tagged}
            />
          </div>
          <div className={classes.privacyBlock}>
            <span className={classes.separateSpans}>
              <span>
                <Link color="inherit" to={link}>
                  <FromNow value={item?.creation_date} />
                </Link>
              </span>
              <PrivacyIcon value={item.privacy} item={item.privacy_detail} />
            </span>
          </div>
        </div>
      </div>
      <FeedStatusView
        status={status}
        backgroundImage={status_background}
        identity={identity}
        isShared={isShared}
      />
    </div>
  );
}
