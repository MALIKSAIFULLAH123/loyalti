import { useGlobal } from '@metafox/framework';
import { LineIcon, TruncateText } from '@metafox/ui';
import { LinearProgress, styled } from '@mui/material';
import React from 'react';

const ChatProgressItem = styled('div', {
  shouldForwardProp: props => props !== 'isAllPage'
})<{ isAllPage?: boolean }>(({ theme, isAllPage }) => ({
  '&:not(:first-of-type)': {
    marginTop: theme.spacing(0.75)
  },
  position: 'relative',
  padding: theme.spacing(1),
  maxWidth: '245px',
  backgroundColor: theme.palette.grey['100'],
  ...(theme.palette.mode === 'dark' && {
    backgroundColor: theme.palette.grey['600']
  }),
  borderRadius: theme.spacing(1.25),
  overflow: 'hidden',
  ...(isAllPage && {
    maxWidth: '400px',
    padding: theme.spacing(1.25, 1)
  })
}));

const ItemInfo = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  '& .ico': {
    marginRight: theme.spacing(0.75)
  }
}));

const ItemText = styled(TruncateText)(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  fontSize: theme.mixins.pxToRem(12)
}));

const ItemBar = styled('div', {
  shouldForwardProp: props => props !== 'isAllPage'
})<{ isAllPage?: boolean }>(({ theme, isAllPage }) => ({
  position: 'absolute',
  bottom: 0,
  left: 0,
  right: 0,
  padding: theme.spacing(0, 1),
  ...(isAllPage && {
    span: {
      height: theme.spacing(0.75)
    }
  })
}));

interface Props {
  count: number;
  files: any;
  progress: any;
  isAllPage?: boolean;
}

function ChatFileProgressItem({
  count = 1,
  files,
  progress = 0,
  isAllPage = false
}: Props) {
  const { i18n } = useGlobal();

  if (!files && !files.length) return;

  return (
    <ChatProgressItem isAllPage={isAllPage}>
      <ItemInfo>
        <LineIcon icon="ico-paperclip-alt" />
        <ItemText lines={1}>
          {count === 1 ? (
            <>
              {i18n.formatMessage(
                { id: 'uploading_name' },
                { name: files[0].name }
              )}
            </>
          ) : (
            i18n.formatMessage(
              { id: 'uploading_n_attachments' },
              {
                count
              }
            )
          )}
        </ItemText>
      </ItemInfo>
      <ItemBar isAllPage={isAllPage}>
        <LinearProgress variant="determinate" value={progress} />
      </ItemBar>
    </ChatProgressItem>
  );
}

export default ChatFileProgressItem;
