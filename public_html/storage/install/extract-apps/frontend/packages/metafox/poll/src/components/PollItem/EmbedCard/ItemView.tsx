import { Link, useGlobal } from '@metafox/framework';
import PollVoteForm from '@metafox/poll/blocks/PollDetail/PollVoteForm';
import { FeedEmbedCard, TruncateText } from '@metafox/ui';
import React from 'react';
import useStyles from './styles';
import HtmlViewer from '@metafox/html-viewer';

export default function EmbedPollInFeedItemView({
  item,
  identity,
  answers,
  feed,
  isShowPendingCard = false,
  isShared
}) {
  const classes = useStyles();
  const { jsxBackend, useLoggedIn } = useGlobal();
  const isLogged = useLoggedIn();

  if (!item) return null;

  const {
    question,
    description,
    public_vote,
    close_time,
    statistic,
    id,
    is_multiple,
    extra,
    is_user_voted,
    is_featured,
    is_sponsor,
    is_closed
  } = item;

  const PendingCard = jsxBackend.get('core.itemView.pendingReviewCard');

  return (
    <FeedEmbedCard
      variant="list"
      bottomSpacing="normal"
      item={item}
      feed={feed}
      isShared={isShared}
    >
      <div className={classes.root}>
        {PendingCard && isShowPendingCard && (
          <PendingCard sxWrapper={{ mb: 2 }} item={item} />
        )}
        <TruncateText variant={'h4'} lines={2} className={classes.title}>
          <Link
            to={item.link}
            children={question}
            identityTracking={feed?._identity}
          />
        </TruncateText>
        {description ? (
          <TruncateText
            variant={'body1'}
            lines={3}
            color="textSecondary"
            className={classes.description}
          >
            <HtmlViewer html={description || ''} />
          </TruncateText>
        ) : null}
        <PollVoteForm
          isVoted={is_user_voted}
          pollId={id}
          isMultiple={is_multiple}
          answers={answers}
          statistic={statistic}
          closeTime={close_time}
          publicVote={public_vote}
          identity={identity}
          canVoteAgain={extra.can_change_vote}
          canVote={isLogged ? extra.can_vote : false}
          canViewResult={extra.can_view_result}
          isEmbedInFeed
          isFeatured={is_featured}
          isSponsor={is_sponsor}
          isClosed={is_closed}
          canViewResultAfter={extra.can_view_result_after_vote}
          canViewResultBefore={extra.can_view_result_before_vote}
          disabled={isShared}
        />
      </div>
    </FeedEmbedCard>
  );
}
