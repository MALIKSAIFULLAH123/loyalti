/**
 * @type: ui
 * name: FeedHistoryItem
 */
import { FromNow, UserAvatar, TruncateViewMore, UserName } from '@metafox/ui';
import { Box, styled } from '@mui/material';
import React from 'react';
import HtmlViewer from '@metafox/html-viewer';

const name = 'Feed';

const ItemInner = styled('div', { name, slot: 'itemInner' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  minWidth: 0,
  wordBreak: 'break-word'
}));
const AvatarWrapper = styled('div', { name, slot: 'AvatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1.5)
  })
);
const ItemName = styled('div', { name, slot: 'ItemName' })(({ theme }) => ({
  display: 'flex',
  fontSize: theme.mixins.pxToRem(13),
  marginBottom: theme.spacing(0.25)
}));
const UserNameStyled = styled(UserName, { name, slot: 'userName' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(13),
    maxWidth: '100%',
    fontWeight: 'bold'
  })
);
const FromNowStyled = styled(FromNow, { name, slot: 'FromNowStyled' })(
  ({ theme }) => ({
    display: 'flex',
    color: theme.palette.text.secondary,
    whiteSpace: 'nowrap',
    alignItems: 'flex-end',
    marginLeft: theme.spacing(1)
  })
);

const PhraseType = styled('div', { name, slot: 'PhraseType' })(({ theme }) => ({
  color: theme.palette.text.hint,
  fontSize: theme.mixins.pxToRem(13),
  marginTop: theme.spacing(0.5)
}));
type Props = {
  item: Record<string, any>;
};
export default function Comment({ item }: Props) {
  if (!item) return null;

  const { user: userItem, content, created_at, phrase } = item;

  return (
    <Box
      data-testid="feed"
      id={`feed-${item.id}`}
      data-author={userItem.full_name}
    >
      <Box sx={{ display: 'flex' }}>
        <AvatarWrapper>
          <UserAvatar user={userItem as any} size={32} />
        </AvatarWrapper>
        <ItemInner>
          <Box sx={{ position: 'relative', zIndex: 2 }}>
            <ItemName>
              <UserNameStyled to={`/user/${userItem.id}`} user={userItem} />
              <FromNowStyled value={created_at} />
            </ItemName>
            <TruncateViewMore
              truncateProps={{
                variant: 'body1',
                lines: 3
              }}
            >
              <HtmlViewer html={content} />
            </TruncateViewMore>
            {phrase && <PhraseType>{phrase}</PhraseType>}
          </Box>
        </ItemInner>
      </Box>
    </Box>
  );
}
