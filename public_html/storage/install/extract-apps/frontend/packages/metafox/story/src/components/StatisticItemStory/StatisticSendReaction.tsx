/**
 * @type: ui
 * name: story.ui.statisticSendReaction
 * chunkName: storyDetail
 */

import React from 'react';
import { Box, Tooltip, styled } from '@mui/material';
import { isEmpty, uniqueId } from 'lodash';
import { useGlobal } from '@metafox/framework';
import { TruncateText } from '@metafox/ui';

const name = 'StatisticSendReaction';

const RootStyled = styled(Box, {
  name,
  slot: 'root',
  shouldForwardProp: props => props !== 'active'
})<{ active?: boolean }>(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  marginTop: theme.spacing(1.5),
  '&:after': {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    height: '42px',
    backgroundImage:
      'linear-gradient(0deg, rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.12) 50%, transparent)',
    content: '""',
    zIndex: 2
  }
}));

const ItemStyled = styled(Box, {
  name,
  slot: 'root',
  shouldForwardProp: props => props !== 'active'
})<{ active?: boolean }>(({ theme }) => ({
  display: 'flex',
  marginRight: theme.spacing(0.5),
  width: '100%',
  '& img': {
    minWidth: '18px'
  }
}));

const SendReactionText = styled(TruncateText, {
  name,
  slot: 'SendReactionText'
})(({ theme }) => ({
  marginLeft: theme.spacing(1),
  flex: 1,
  minWidth: 0,
  zIndex: 3
}));
const WrapperIcon = styled(Box, { name, slot: 'WrapperIcon' })(({ theme }) => ({
  display: 'flex',
  zIndex: 3
}));

const Item = ({ identity }: { identity: any }) => {
  const { useGetItem } = useGlobal();
  const item = useGetItem(identity);

  if (isEmpty(item)) return null;

  return (
    <ItemStyled aria-label={item.title} data-testid="itemReaction">
      <Tooltip title={item.title}>
        <img
          role="button"
          aria-label={item.title}
          data-testid="itemReaction"
          src={item.icon}
          draggable={false}
          width="18px"
          height="18px"
          alt={item.title}
        />
      </Tooltip>
    </ItemStyled>
  );
};

function StatisticSendReaction({ identity }: any) {
  const { i18n, useGetItem } = useGlobal();

  const story = useGetItem(identity);
  const user = useGetItem(story?.user);
  const { reactions } = story || {};

  if (isEmpty(reactions) || isEmpty(story)) return null;

  return (
    <RootStyled data-testid="sendReaction">
      <WrapperIcon>
        {reactions.slice(0, 5).map(identity => {
          return <Item key={uniqueId(identity)} identity={identity} />;
        })}
      </WrapperIcon>
      {user ? (
        <SendReactionText
          component="span"
          variant="body2"
          lines={1}
          style={{ color: '#fff' }}
        >
          {i18n.formatMessage(
            { id: 'sent_reation_to_user' },
            { name: user?.full_name }
          )}
        </SendReactionText>
      ) : null}
    </RootStyled>
  );
}

export default React.memo(
  StatisticSendReaction,
  (prev, next) => prev?.identity === next?.identity
);
