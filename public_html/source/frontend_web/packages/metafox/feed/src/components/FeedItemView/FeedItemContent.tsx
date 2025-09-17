/**
 * @type: itemView
 * name: feed.itemView.content
 */
import PrivacyControl from '@metafox/feed/dialogs/StatusComposer/PrivacyControl';
import { getTaggedFriendsSelector } from '@metafox/feed/selectors/feed';
import {
  getItemSelector,
  GlobalState,
  Link,
  useGetItem,
  useGlobal
} from '@metafox/framework';
import {
  FromNow,
  ItemUserShape,
  PrivacyIcon,
  RoleLabel,
  UserAvatar,
  LineIcon,
  FormatDate
} from '@metafox/ui';
import { styled, Typography, Box } from '@mui/material';
import * as React from 'react';
import { useSelector } from 'react-redux';
import { FeedItemShape, FeedItemViewState } from '../../types';
import FeedEmbedObjectView from '../EmbedObject';
import FeedStatus from '../FeedStatus';
import HeadlineInfo from '../HeadlineInfo';
import HeadlineSpan from '../HeadlineSpan';
import { LoadingSkeleton } from './LoadingSkeleton';
import ProfileLink from './ProfileLink';
import useStyles from './styles';
import { TopDivider } from '../Components';

export const AvatarWrapper = styled('div', { name: 'AvatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1.5)
  })
);

const FeedItemHeader = styled('div', {
  name: 'FeedItem',
  slot: 'feedItemHeader',
  overridesResolver(props, styles) {
    return [styles.feedItemHeader];
  }
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  marginBottom: theme.spacing(1.5),
  '& button:hover': {
    backgroundColor: theme.palette.action.selected
  }
}));

const FeedTop = styled(Typography, {
  name: 'FeedItem',
  slot: 'feedItemHeaderTop',
  overridesResolver(props, styles) {
    return [styles.feedTop];
  }
})(({ theme }) => ({}));

const FeaturedIcon = styled(LineIcon, { slot: 'FeaturedIcon' })(
  ({ theme }) => ({
    color: theme.palette.primary.main,
    marginLeft: theme.spacing(1),
    marginRight: theme.spacing(0.5)
  })
);

type Props = {
  handleAction: any;
  state: FeedItemViewState;
  menuName?: string;
  identity: string;
  setVisible: (value: boolean) => void;
  isItemAction?: boolean;
};

const FeedItemContent = ({
  identity,
  handleAction,
  state,
  menuName = 'itemActionMenu',
  setVisible,
  isItemAction = true
}: Props) => {
  const classes = useStyles();
  const { ItemActionMenu, dispatch, usePageParams, jsxBackend } = useGlobal();
  const { module_name } = usePageParams();
  const item: FeedItemShape = useSelector((state: GlobalState) =>
    getItemSelector(state, identity)
  );
  const user: ItemUserShape = useSelector((state: GlobalState) =>
    getItemSelector(state, item?.user)
  );
  const embed_object = useSelector((state: GlobalState) =>
    getItemSelector(state, item?.embed_object)
  );
  const tagged_friends = useSelector((state: GlobalState) =>
    getTaggedFriendsSelector(state, item)
  );
  const parent_user = useGetItem(item?.parent_user);

  if (!item || !user) return null;

  const {
    info,
    item_id,
    status,
    status_background,
    location,
    is_show_location = true,
    item_type,
    total_friends_tagged,
    extra,
    link,
    role_label,
    schedule_time
  } = item;
  const showMapview = !!location?.show_map && !item?.embed_object;

  return (
    <>
      {schedule_time && (
        <FeedTop
          variant="body2"
          color="text.secondary"
          paddingBottom={2}
          display="inline-flex"
          alignItems={'center'}
          data-testid="publishedDate"
        >
          <LineIcon icon={'ico-clock-o'} sx={{ mr: 0.5 }} />
          <FormatDate
            phrase={'will_send_on_time'}
            data-testid="publishedDate"
            value={schedule_time}
            format="llll"
          />
        </FeedTop>
      )}
      <FeedItemHeader data-testid="feedItemHeader">
        <AvatarWrapper>
          <UserAvatar user={user as any} size={48} />
        </AvatarWrapper>
        <div className={classes.headerInfo}>
          <div
            data-testid="feedHeaderHeadline"
            className={classes.headerHeadline}
          >
            <HeadlineSpan>
              <ProfileLink user={user} className={classes.profileLink} />
              {user.is_featured ? (
                <FeaturedIcon icon="ico-check-circle" />
              ) : null}{' '}
            </HeadlineSpan>
            <HeadlineInfo
              info={info}
              embed_object={embed_object}
              item={item}
              classes={classes}
              tagged_friends={tagged_friends}
              location={location}
              is_show_location={is_show_location}
              item_type={item_type}
              item_id={item_id}
              total_friends_tagged={total_friends_tagged}
            />
          </div>
          <div data-testid="feedPrivacyBlock" className={classes.privacyBlock}>
            {module_name === parent_user?.module_name && role_label && (
              <RoleLabel
                data-testid={'feedRoleLabel'}
                text={role_label}
                sx={{ mr: 1 }}
              />
            )}
            <span className={classes.separateSpans}>
              <span>
                <Link
                  data-testid={'feedCreationDate'}
                  color="inherit"
                  to={link}
                >
                  <FromNow shorten value={item.creation_date} />
                </Link>
              </span>
              {extra?.can_change_privacy_from_feed ? (
                <span>
                  <PrivacyControl
                    showDropdown={false}
                    setValue={value => {
                      dispatch({
                        type: 'updatePrivacyFeedItem',
                        payload: { identity, privacy: value }
                      });
                    }}
                    value={item.privacy}
                    item={item.privacy_detail}
                    showLabel={false}
                    feed={item}
                  />
                </span>
              ) : (
                <PrivacyIcon item={item.privacy_detail} />
              )}
            </span>
          </div>
        </div>
        {isItemAction && (
          <ItemActionMenu
            identity={identity}
            state={state}
            handleAction={handleAction}
            className={classes.headerActionMenu}
            menuName={menuName}
            zIndex={999}
          />
        )}
      </FeedItemHeader>
      <FeedStatus
        status={status}
        backgroundImage={status_background}
        identity={identity}
      />
      {showMapview ? (
        <Box mb={2}>
          {jsxBackend.render({
            component: 'ui.mapDisplay',
            props: { location }
          })}
        </Box>
      ) : null}
      {!embed_object && item.embed_object?.length !== 0 ? <TopDivider /> : null}
      <FeedEmbedObjectView
        embed={item.embed_object}
        feed={item}
        setVisible={setVisible}
      />
    </>
  );
};

FeedItemContent.LoadingSkeleton = LoadingSkeleton;

export default FeedItemContent;
