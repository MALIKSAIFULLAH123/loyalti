/**
 * @type: ui
 * name: FeedItemActionInteract
 * chunkName: feed
 */
import { useGlobal } from '@metafox/framework';
import { FeedStatistic } from '@metafox/ui';
import { styled } from '@mui/material';
import * as React from 'react';

type Props = {
  identity: string;
  item: Record<string, any>;
  user: Record<string, any>;
  handleAction: () => void;
  handleLayoutWithAction: () => void;
};

const name = 'FeedItem';

const FeedReactionRoot = styled('div', {
  name,
  slot: 'FeedReactionRoot',
  overridesResolver(props, styles) {
    return [styles.root];
  }
})(({ theme }) => ({
  minHeight: theme.spacing(2),
  display: 'flex',
  alignItems: 'center',
  flexDirection: 'column-reverse'
}));

const Wrapper = styled('div', {
  name,
  slot: 'Wrapper',
  overridesResolver(props, styles) {
    return [styles.root];
  }
})(({ theme }) => ({
  '& .MuiFeedCommentBlock-itemOuter': {
    border: 'none'
  },
  width: '100%'
}));

const StatisticWrapper = styled('div', {
  name,
  slot: 'StatisticWrapper',
  overridesResolver(props, styles) {
    return [styles.root];
  }
})(({ theme }) => ({
  flex: 1,
  display: 'flex',
  justifyContent: 'flex-end',
  width: '100%',
  padding: theme.spacing(0.5, 0),
  '&:not(:empty)': {
    borderBottom: 'solid 1px',
    borderBottomColor: theme.palette.border?.secondary
  },
  '&:empty': {
    padding: 0
  }
}));

const FeedItemActionInteract = ({
  identity,
  item,
  user,
  handleAction,
  handleLayoutWithAction
}: Props) => {
  const {
    CommentActButton,
    ShareActButton,
    ReactionActButton,
    CommentReaction,
    useSession
  } = useGlobal();
  const session = useSession();

  if (!item || !user) return null;

  const { statistic, most_reactions_information, like_phrase } = item;

  return (
    <FeedReactionRoot>
      <Wrapper>
        {CommentReaction ? (
          <CommentReaction>
            {session.loggedIn && item.extra?.can_like && ReactionActButton ? (
              <ReactionActButton
                reacted={item.user_reacted}
                identity={identity}
                handleAction={handleAction}
              />
            ) : null}
            {session.loggedIn && item.extra?.can_comment && CommentActButton ? (
              <CommentActButton
                identity={identity}
                handleAction={handleLayoutWithAction}
              />
            ) : null}
            {session.loggedIn && item.extra.can_share && ShareActButton ? (
              <ShareActButton handleAction={handleAction} identity={identity} />
            ) : null}
          </CommentReaction>
        ) : null}
      </Wrapper>
      <StatisticWrapper>
        <FeedStatistic
          handleAction={handleLayoutWithAction}
          identity={identity}
          reactions={most_reactions_information}
          message={like_phrase}
          statistic={statistic}
        />
      </StatisticWrapper>
    </FeedReactionRoot>
  );
};

export default FeedItemActionInteract;
