import { Link, useGlobal } from '@metafox/framework';
import React from 'react';
import HtmlViewer from '@metafox/html-viewer';
import { AttachmentItem, FeedEmbedCard, TruncateViewMore } from '@metafox/ui';
import { styled, Box } from '@mui/material';

const name = 'PostItemDetailCard';

const PostContent = styled('div', { name, slot: 'postContent' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(15),
    lineHeight: 1.33,
    '& p + p': {
      marginBottom: theme.spacing(2.5)
    }
  })
);

const AttachmentTitle = styled('div', { name, slot: 'attachmentTitle' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(18),
    marginTop: theme.spacing(2),
    color: theme.palette.text.secondary,
    fontWeight: theme.typography.fontWeightBold
  })
);
const Attachment = styled('div', { name, slot: 'attachment' })(({ theme }) => ({
  width: '100%',
  display: 'flex',
  flexWrap: 'wrap',
  justifyContent: 'space-between',
  marginBottom: theme.spacing(2)
}));
const AttachmentItemWrapper = styled('div', {
  name,
  slot: 'attachmentItemWrapper'
})(({ theme }) => ({
  marginTop: theme.spacing(2),
  flexGrow: 0,
  flexShrink: 0,
  flexBasis: 'calc(50% - 8px)',
  minWidth: 300
}));

export default function PostItemDetailCard({
  item,
  identity,
  itemProps,
  user,
  feed,
  isShared
}) {
  const { useGetItem, useGetItems, i18n, useIsMobile } = useGlobal();
  const attachments = useGetItems(item?.attachments);
  const { content: description, thread: threadIdentity } = item;
  const thread = useGetItem(threadIdentity);
  const to = thread ? `/forum/thread/${thread?.id}` : '';
  const isMobile = useIsMobile();

  return (
    <FeedEmbedCard
      bottomSpacing="normal"
      item={item}
      feed={feed}
      isShared={isShared}
    >
      <Box sx={{ width: '100%' }} px={2} pb={2}>
        <PostContent>
          <TruncateViewMore
            truncateProps={{
              variant: isMobile ? 'body2' : 'body1',
              lines: 3
            }}
          >
            <HtmlViewer html={description || ''} />
          </TruncateViewMore>
        </PostContent>
        {attachments?.length > 0 && (
          <>
            <AttachmentTitle>
              {i18n.formatMessage({ id: 'attachments' })}
            </AttachmentTitle>
            <Attachment>
              {attachments.map((item, index) => (
                <AttachmentItemWrapper key={item.id.toString()}>
                  <AttachmentItem
                    fileName={item.file_name}
                    downloadUrl={item.download_url}
                    isImage={item.is_image}
                    fileSizeText={item.file_size_text}
                    size="large"
                    image={item?.image}
                    index={index}
                  />
                </AttachmentItemWrapper>
              ))}
            </Attachment>
          </>
        )}
        <Box sx={{ color: 'text.secondary', pt: 1 }}>
          <span>{i18n.formatMessage({ id: 'parent_thread' })}: </span>
          <Link color={'primary'} to={to}>
            {thread.title}
          </Link>
        </Box>
      </Box>
    </FeedEmbedCard>
  );
}
PostItemDetailCard.displayName = 'ForumPostItem(PostItemDetailCard)';
