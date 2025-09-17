import { useGlobal } from '@metafox/framework';
import { ItemView, LineIcon } from '@metafox/ui';
import { Divider, styled, Typography } from '@mui/material';
import * as React from 'react';
import { FeedItemViewProps as Props } from '../../types';
import FeedItemContent from './FeedItemContent';
import FeedItemHiddenView from './FeedItemHiddenView';
import { LoadingSkeleton } from './LoadingSkeleton';
import useStyles from './styles';
import { isArray } from 'lodash';
import FeedCommentListing from './FeedCommentListing';
import { BottomDivider } from '../Components';

export const AvatarWrapper = styled('div', { name: 'AvatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1.5)
  })
);

const ItemViewStyled = styled(ItemView, { name: 'ItemView' })(({ theme }) => ({
  overflow: 'inherit'
}));

const Notice = styled('div', { name: 'TableStyled' })(({ theme }) => ({
  textAlign: 'center',
  padding: theme.spacing(2),
  color: theme.palette.text.hint,
  fontWeight: theme.typography.fontWeightSemiBold
}));

const FeedTop = styled(Typography, {
  name: 'FeedItem',
  slot: 'FeedTop',
  overridesResolver(props, styles) {
    return [styles.feedTop];
  }
})(({ theme }) => ({}));

const FeedItemView = ({
  identity,
  item,
  user,
  actions,
  handleAction,
  state,
  wrapAs,
  wrapProps,
  itemProps,
  parent_user,
  isDetailFeed = false
}: Props) => {
  const {
    i18n,
    useSession,
    dialogBackend,
    useIsMobile,
    usePageParams,
    getSetting,
    jsxBackend
  } = useGlobal();
  const classes = useStyles();
  const session = useSession();
  const pageParams = usePageParams();
  const myRef = React.useRef(null);

  const [visible, setVisible] = React.useState<boolean>(true);
  const enableCommentApp = getSetting('comment');
  const isMobile = useIsMobile();

  React.useEffect(() => {
    if (item?.is_just_hide)
      myRef.current?.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
        inline: 'nearest'
      });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [item?.is_just_hide]);

  if (!item || !user) return null;

  const { statistic, is_sponsor, type_id } = item;

  const { menuName = 'itemActionMenu' } = itemProps;

  const allowComment = getSetting(`feed.types.${type_id}.allow_comment`);
  const canComment = item.extra?.can_comment;

  const handleLayoutWithAction = (type?: string, payload?: unknown) => {
    const acceptTypes = ['toggleItemComments', 'onPressedCommentActButton'];

    if (!showCommentList && acceptTypes.includes(type)) {
      dialogBackend.present({
        component: 'comment.dialog.commentList',
        props: {
          identity,
          isFocus: type === 'onPressedCommentActButton',
          handleAction,
          viewMoreComments: actions.viewMoreComments
        }
      });

      return;
    }

    handleAction(type, payload);
  };

  const handleClickComposer: React.MouseEventHandler<HTMLDivElement> = e => {
    if (showCommentList) return;

    e.stopPropagation();
    handleLayoutWithAction('onPressedCommentActButton');
  };

  if (!visible) return null;

  let status = '';

  if (enableCommentApp && session.loggedIn && allowComment && !canComment) {
    status = i18n.formatMessage({
      id: 'comment_turn_off'
    });

    if (!item.extra?.can_like && !item.extra?.can_share) {
      status = i18n.formatMessage({ id: 'read_only_post' });
    }

    if (
      statistic?.total_comment ||
      statistic?.total_like ||
      statistic?.total_share
    ) {
      status = i18n.formatMessage({
        id: 'comment_turn_off'
      });
    }
  }

  const isPinned =
    isArray(item.pins) &&
    // eslint-disable-next-line eqeqeq
    item.pins.findIndex(pinId => pinId == pageParams.profile_id) >= 0;

  const showCommentList = !isMobile || isDetailFeed;
  const isShowSponsoredIcon = is_sponsor && !isPinned;

  return (
    <ItemViewStyled
      wrapProps={wrapProps}
      wrapAs={wrapAs}
      testid={`${item.resource_name}`}
      data-eid={identity}
      ref={myRef}
      id={`homefeed_${identity}`}
      identity={identity}
    >
      {item.is_just_hide ? (
        <FeedItemHiddenView
          item={item}
          classes={classes}
          handleAction={handleAction}
        />
      ) : (
        <>
          {isPinned && (
            <FeedTop
              variant="body2"
              color="text.secondary"
              paddingBottom={2}
              display="inline-flex"
              alignItems={'center'}
              data-testid="pinnedPost"
            >
              <LineIcon icon="ico-thumb-tack-o" sx={{ paddingRight: 1 }} />
              {i18n.formatMessage({ id: 'pinned_post' })}
            </FeedTop>
          )}
          {isShowSponsoredIcon ? (
            <FeedTop
              variant="body2"
              color="text.secondary"
              paddingBottom={2}
              display="inline-flex"
              alignItems={'center'}
              data-testid="sponsorPost"
            >
              <LineIcon icon="ico-sponsor" sx={{ paddingRight: 1 }} />
              {i18n.formatMessage({ id: 'sponsored' })}
            </FeedTop>
          ) : null}
          <FeedItemContent
            menuName={menuName}
            identity={identity}
            state={state}
            handleAction={handleAction}
            setVisible={setVisible}
          />
          {jsxBackend.render({
            component: 'FeedItemActionInteract',
            props: {
              identity,
              item,
              user,
              handleAction,
              handleLayoutWithAction
            }
          })}
          {(statistic?.total_comment || item.extra?.can_comment) &&
          state.commentOpened &&
          session.loggedIn &&
          showCommentList ? (
            <BottomDivider />
          ) : null}
          <FeedCommentListing
            handleClickComposer={handleClickComposer}
            identity={identity}
            item={item}
            actions={actions}
            handleAction={handleAction}
            state={state}
            parent_user={parent_user}
            isDetailFeed={isDetailFeed}
          />
          {status !== '' ? (
            <div>
              {(statistic?.total_like ||
                statistic?.total_share ||
                item.extra?.can_like ||
                item.extra?.can_share ||
                item.extra?.can_comment) &&
              !statistic?.total_comment ? (
                <Divider />
              ) : null}
              <Notice>{status}</Notice>
            </div>
          ) : null}
        </>
      )}
    </ItemViewStyled>
  );
};

FeedItemView.LoadingSkeleton = LoadingSkeleton;

export default FeedItemView;
