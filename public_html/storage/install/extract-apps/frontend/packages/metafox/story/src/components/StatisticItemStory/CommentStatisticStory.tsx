/**
 * @type: ui
 * name: story.ui.commentStatisticStory
 * chunkName: storyDetail
 */

import React from 'react';
import { Box, styled } from '@mui/material';
import { isEmpty, uniqueId } from 'lodash';
import { useGlobal } from '@metafox/framework';
import { TruncateText, UserAvatar, UserName } from '@metafox/ui';
import HtmlViewer from '@metafox/html-viewer';
import CommentExtraData from './CommentExtraData';
import { useStoryViewContext } from '@metafox/story/hooks';

const name = 'CommentStatisticStory';

const RootStyled = styled(Box, {
  name,
  slot: 'root',
  shouldForwardProp: props => props !== 'active'
})<{ active?: boolean }>(({ theme, active }) => ({
  display: 'flex',
  flexDirection: 'column'
}));

const ItemOuter = styled('div', {
  name,
  slot: 'itemOuter'
})(({ theme }) => ({
  width: 'fit-content',
  display: 'flex',
  marginTop: theme.spacing(1),
  backgroundColor: 'rgba(0, 0, 0, 0.4)',
  padding: theme.spacing(0.75, 1.5),
  borderRadius: 40,
  maxWidth: '100%',
  cursor: 'pointer'
}));
const ItemInner = styled('div', { name, slot: 'itemInner' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  minWidth: 0,
  wordBreak: 'break-word'
}));
const AvatarWrapper = styled('div', { name, slot: 'AvatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1),
    display: 'flex',
    alignItems: 'center'
  })
);
const ItemName = styled(TruncateText, { name, slot: 'ItemName' })(
  ({ theme }) => ({
    display: 'flex',
    fontSize: theme.mixins.pxToRem(13),
    alignItems: 'center'
  })
);
const UserNameStyled = styled(UserName, { name, slot: 'userName' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(13),
    maxWidth: '100%',
    pointerEvents: 'none'
  })
);
const BoxWrapper = styled(Box, {
  name,
  slot: 'BoxMessageWrapper',
  overridesResolver(props, styles) {
    return [styles.boxMessageWrapper];
  }
})(({ theme }) => ({
  color: '#fff',
  position: 'relative',
  '&:before': {
    content: '""',
    position: 'absolute',
    left: '-4px',
    top: '-4px',
    right: '-4px',
    bottom: '-4px',
    borderRadius: theme.shape.borderRadius,
    transition: 'background 300ms ease',
    background: 'none',
    pointerEvents: 'none'
  }
}));

const BubbleText = styled('div', {
  name: 'CommentContent',
  slot: 'bubbleText'
})(({ theme }) => ({
  display: 'block',
  fontSize: theme.mixins.pxToRem(15),
  transition: 'background 300ms ease',
  '& a': {
    color: 'rgba(255, 255, 255, 0.9)',
    fontWeight: theme.typography.fontWeightRegular,
    PointerEvent: 'none'
  }
}));

const Item = ({ identity }: { identity: any }) => {
  const { useGetItem } = useGlobal();
  const item = useGetItem(identity);
  const user = useGetItem(item?.user);

  const { openStoryDetail, fire, openViewComment } = useStoryViewContext();

  const handleViewComment = () => {
    if (openStoryDetail) {
      fire({ type: 'setOpenStoryDetail', payload: false });
    }

    fire({ type: 'setOpenViewComment', payload: !openViewComment });
  };

  if (isEmpty(item)) return null;

  const { text, extra_data } = item || {};

  return (
    <ItemOuter data-testid="statisticStory" onClick={handleViewComment}>
      <AvatarWrapper>
        <UserAvatar
          user={user as any}
          size={32}
          noStory
          noLink
          hoverCard={false}
        />
      </AvatarWrapper>
      <ItemInner>
        <BoxWrapper p={0} borderRadius={2}>
          <Box sx={{ position: 'relative', zIndex: 2 }}>
            <ItemName variant="h5" lines={1} style={{ fontSize: '13px' }}>
              <UserNameStyled
                to={`/user/${user.id}`}
                user={user}
                hoverCard={false}
              />
            </ItemName>
            <Box>
              {text ? (
                <BubbleText>
                  <TruncateText
                    variant="body1"
                    lines={2}
                    style={{
                      fontSize: '13px',
                      color: 'rgba(255, 255, 255, 0.9)'
                    }}
                  >
                    <HtmlViewer html={text} />
                  </TruncateText>
                </BubbleText>
              ) : null}
              <CommentExtraData text={text} extra_data={extra_data} />
            </Box>
          </Box>
        </BoxWrapper>
      </ItemInner>
    </ItemOuter>
  );
};

function CommentStatisticStory({ identity }: any) {
  const { useGetItem } = useGlobal();

  const story = useGetItem(identity);
  const { related_comments } = story || {};

  if (isEmpty(related_comments) || isEmpty(story)) return null;

  return (
    <RootStyled>
      {related_comments.slice(0, 3).map(identity => {
        return <Item key={uniqueId(identity)} identity={identity} />;
      })}
    </RootStyled>
  );
}

export default React.memo(
  CommentStatisticStory,
  (prev, next) => prev?.identity === next?.identity
);
