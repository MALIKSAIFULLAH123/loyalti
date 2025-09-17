/**
 * @type: ui
 * name: feed.ui.modalCard
 */
import { FeedItemViewProps } from '@metafox/feed/types';
import { useGlobal } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { ScrollContainer } from '@metafox/layout';
import {
  FeedStatistic,
  FromNow,
  LineIcon,
  UserAvatar,
  UserName
} from '@metafox/ui';
import { Skeleton, Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import * as React from 'react';
import CommentList from '@metafox/feed/components/CommentList';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        borderRadius: theme.shape.borderRadius,
        backgroundColor: theme.palette.background.paper,
        padding: theme.spacing(2),
        paddingBottom: 0,
        display: 'flex',
        flexFlow: 'column',
        height: '100%',
        maxHeight: '600px',
        justifyContent: 'space-between',
        [theme.breakpoints.down('xs')]: {
          width: '100%',
          maxHeight: '400px'
        }
      },
      rootSkeleton: {
        padding: theme.spacing(2)
      },
      header: {
        display: 'flex',
        flexDirection: 'row',
        marginBottom: theme.spacing(2)
      },
      headerInfo: {
        padding: '4px 0',
        flex: 1
      },
      headerAvatarHolder: {
        paddingRight: theme.spacing(1.5)
      },
      profileLink: {
        fontWeight: theme.typography.fontWeightBold,
        paddingRight: theme.spacing(0.5),
        color: theme.palette.text.primary
      },
      privacyBlock: {
        display: 'flex',
        flexDirection: 'row',
        alignItems: 'center',
        color: theme.palette.text.secondary
      },
      separateSpans: {
        display: 'flex',
        alignItems: 'center',
        '& span + span:before': {
          content: '"·"',
          display: 'inline-block',
          padding: `${theme.spacing(0, 0.5)}`
        }
      },
      body: {
        flexGrow: 1
      },
      content: {
        flexGrow: 1,
        display: 'flex',
        flexFlow: 'column wrap'
      },
      commentListing: {
        flexGrow: 1,
        overflow: 'hidden'
      },
      commentReaction: {
        flexGrow: 'initial'
      },
      info: {
        marginBottom: theme.spacing(2),
        fontSize: theme.mixins.pxToRem(15),
        color: theme.palette.text.primary
      }
    }),
  { name: 'MuiFeedItemViewModal' }
);

const FeedItemView = ({
  item,
  user,
  identity,
  itemProps
}: FeedItemViewProps) => {
  const { info, statistic, most_reactions_information, item_type, item_id } =
    item;
  const { useActionControl } = useGlobal();
  const commentInputRef = React.useRef();
  const {
    ReactionActButton,
    CommentReaction,
    CommentActButton,
    ShareActButton,
    ItemActionMenu,
    jsxBackend
  } = useGlobal();
  const classes = useStyles();

  const [handleAction, state] = useActionControl(identity, {
    menuOpened: false,
    commentOpened: true,
    commentInputRef
  });

  if (!item) return null;

  const CommentComposer = jsxBackend.get('CommentComposer');

  return (
    <div className={classes.root}>
      <div className={classes.content}>
        <div className={classes.header}>
          <div className={classes.headerAvatarHolder}>
            <UserAvatar user={user as any} size={48} />
          </div>
          <div className={classes.headerInfo}>
            <div>
              <UserName user={user} className={classes.profileLink} />
            </div>
            <div className={classes.privacyBlock}>
              <span className={classes.separateSpans}>
                <LineIcon
                  icon="ico-globe-o"
                  style={{ fontSize: 12 }}
                  aria-label="share with public"
                  role="img"
                />
                <FromNow value={item.creation_date} />
              </span>
            </div>
          </div>
          <ItemActionMenu
            identity={identity}
            state={state}
            handleAction={handleAction}
          />
        </div>
        <div className={classes.body}>
          <ScrollContainer autoHide>
            {info ? (
              <div className={classes.info}>
                <HtmlViewer html={info} />
              </div>
            ) : null}
            <FeedStatistic
              handleAction={handleAction}
              identity={identity}
              reactions={most_reactions_information}
              statistic={statistic}
            />
            {CommentReaction ? (
              <CommentReaction>
                {item.extra?.can_like ? (
                  <ReactionActButton
                    reacted={item.user_reacted}
                    identity={identity}
                    handleAction={handleAction}
                  />
                ) : null}
                {item.extra?.can_comment ? (
                  <CommentActButton
                    identity={identity}
                    handleAction={handleAction}
                  />
                ) : null}
                {item.extra.can_share ? (
                  <ShareActButton
                    handleAction={handleAction}
                    identity={identity}
                  />
                ) : null}
              </CommentReaction>
            ) : null}
            <CommentList
              open
              handleAction={handleAction}
              item_type={item_type}
              item_id={item_id}
              className={classes.commentListing}
              isDetailPage
            />
          </ScrollContainer>
        </div>
      </div>
      <CommentComposer open={state.commentOpened} />
    </div>
  );
};

const LoadingSkeleton = ({ itemProps }) => {
  const classes = useStyles();

  return (
    <div className={`${classes.root} ${classes.rootSkeleton}`}>
      <div className={classes.header}>
        <div className={classes.headerAvatarHolder}>
          <Skeleton variant="circular" width={40} height={40} />
        </div>
        <div className={classes.headerInfo}>
          <div>
            <Skeleton variant="text" component="div" />
          </div>
          <div className={classes.privacyBlock}>
            <Skeleton variant="text" width={120} />
          </div>
        </div>
      </div>
      <div>
        <Skeleton variant="text" />
        <Skeleton variant="text" />
        <Skeleton variant="text" />
      </div>
    </div>
  );
};

FeedItemView.LoadingSkeleton = LoadingSkeleton;

export default FeedItemView;
