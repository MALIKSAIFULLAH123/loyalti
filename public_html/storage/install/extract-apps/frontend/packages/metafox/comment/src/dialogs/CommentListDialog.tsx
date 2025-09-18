/**
 * @type: dialog
 * name: comment.dialog.commentList
 */

import { Dialog, DialogContent, DialogTitle, useDialog } from '@metafox/dialog';
import { getItemSelector, GlobalState, useGlobal } from '@metafox/framework';
import { FeedStatistic, LineIcon } from '@metafox/ui';
import { Button, Divider, styled, Typography, Box } from '@mui/material';
import React, { useRef } from 'react';
import { useSelector } from 'react-redux';

const name = 'CommentDialog';

const DialogStyled = styled(Dialog, { name, slot: 'DialogStyled' })(
  ({ theme }) => ({
    [theme.breakpoints.down('sm')]: {
      '& .MuiDialog-container': {
        position: 'fixed',
        zIndex: 1300,
        right: 0,
        bottom: 0,
        top: 0,
        left: 0
      }
    }
  })
);

const ButtonBack = styled(Button, { name, slot: 'btnBack' })(({ theme }) => ({
  color: `${theme.palette.text.hint} !important`,
  [theme.breakpoints.down('sm')]: {
    paddingRight: `${theme.spacing(2.5)} !important`
  }
}));
const DialogContentStyled = styled(DialogContent, {
  name,
  slot: 'DialogContentStyled'
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  borderRadius: theme.shape.borderRadius,
  backgroundColor: theme.palette.background.paper,
  padding: theme.spacing(0, 2),
  paddingBottom: 0,
  [theme.breakpoints.down('sm')]: {
    borderRadius: 0
  }
}));

const CommentBoxWrapper = styled(Box, {
  name,
  slot: 'CommentBoxWrapper'
})(({ theme }) => ({
  backgroundColor: theme.palette.background.paper,
  display: 'block',
  left: 0,
  right: 0,
  bottom: 0,
  zIndex: theme.zIndex.modal
}));
const ActionButtonStaticsWrapper = styled(Box, {
  name,
  slot: 'actionButtonStaticsWrapper'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  borderBottom: 'solid 1px',
  borderBottomColor: theme.palette.border?.secondary,
  padding: 0,
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column-reverse'
  }
}));
const ReactionWrapper = styled(Box, {
  name,
  slot: 'reactionWrapper'
})(({ theme }) => ({
  padding: 0,
  '& .MuiFeedCommentBlock-itemOuter': {
    border: 'none'
  },
  [theme.breakpoints.down('sm')]: {
    width: '100%'
  }
}));
const FeedStatisticWrapper = styled(Box, {
  name,
  slot: 'FeedStatisticWrapper'
})(({ theme }) => ({
  flex: 1,
  display: 'flex',
  justifyContent: 'flex-end',
  padding: '8px 0',
  [theme.breakpoints.down('sm')]: {
    width: '100%',
    fontWeight: theme.typography.fontWeightBold,
    borderBottom: 'solid 1px',
    borderBottomColor: theme.palette.border?.secondary
  }
}));

export default function CommentDialog({
  identity,
  handleAction,
  isFocus,
  viewMoreComments
}: any) {
  const statisticRef = useRef(null);
  const titleRef = useRef(null);
  const contentRef = useRef(null);

  const { dialogProps, closeDialog } = useDialog();
  const {
    i18n,
    CommentList,
    CommentActButton,
    ShareActButton,
    ReactionActButton,
    CommentReaction,
    useSession,
    useTheme,
    jsxBackend,
    useSortComment
  } = useGlobal();
  const theme = useTheme();
  const session = useSession();
  const [sortType, setSortType] = useSortComment();

  const CommentComposer = jsxBackend.get('CommentComposer');

  const item = useSelector<GlobalState>(state =>
    getItemSelector(state, identity)
  ) as any;

  const { statistic, most_reactions_information, like_phrase } = item as any;

  return (
    <DialogStyled {...dialogProps} scroll="body">
      <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
        <DialogTitle ref={titleRef} disableClose>
          <div>
            <ButtonBack
              onClick={closeDialog}
              sx={{ minWidth: 0, paddingRight: theme.spacing(2.5) }}
              color="secondary"
              size="medium"
            >
              <LineIcon icon={'ico-angle-left'} />
            </ButtonBack>
            <Typography component="span" fontWeight="bold">
              {i18n.formatMessage({ id: 'comments' })}
            </Typography>
          </div>
        </DialogTitle>
        <DialogContentStyled ref={contentRef}>
          <ActionButtonStaticsWrapper ref={statisticRef}>
            <ReactionWrapper>
              {CommentReaction ? (
                <CommentReaction>
                  {session.loggedIn &&
                  item.extra?.can_like &&
                  ReactionActButton ? (
                    <ReactionActButton
                      reacted={item.user_reacted}
                      identity={identity}
                      handleAction={handleAction}
                    />
                  ) : null}
                  {session.loggedIn &&
                  item.extra?.can_comment &&
                  CommentActButton ? (
                    <CommentActButton
                      identity={identity}
                      handleAction={handleAction}
                    />
                  ) : null}
                  {session.loggedIn &&
                  item.extra.can_share &&
                  ShareActButton ? (
                    <ShareActButton
                      handleAction={handleAction}
                      identity={identity}
                    />
                  ) : null}
                </CommentReaction>
              ) : null}
            </ReactionWrapper>
            <FeedStatisticWrapper>
              <FeedStatistic
                handleActionCommentStatistic={false}
                handleAction={handleAction}
                identity={identity}
                reactions={most_reactions_information}
                message={like_phrase}
                statistic={statistic}
              />
            </FeedStatisticWrapper>
          </ActionButtonStaticsWrapper>
          <div style={{ flex: 1, minHeight: 0 }}>
            <Box
              sx={{
                maxHeight: '100%',
                overflow: 'auto'
              }}
            >
              <CommentList
                identity={identity}
                handleAction={handleAction}
                data={item.related_comments}
                viewMoreComments={viewMoreComments}
                total_comment={statistic?.total_comment}
                total_reply={statistic?.total_reply}
                setSortType={setSortType}
                sortType={sortType}
                isDetailPage
              />
            </Box>
          </div>
          {session.loggedIn && item.extra.can_comment && CommentComposer ? (
            <CommentBoxWrapper>
              <Divider sx={{ display: { xs: 'block', md: 'none' } }} />
              <CommentComposer identity={identity} open />
            </CommentBoxWrapper>
          ) : null}
        </DialogContentStyled>
      </Box>
    </DialogStyled>
  );
}
