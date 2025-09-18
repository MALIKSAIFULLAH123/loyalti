/**
 * @type: ui
 * name: forum_post.ui.quoteItem
 */

import { useGlobal, Link } from '@metafox/framework';
import React from 'react';
import HtmlViewer from '@metafox/html-viewer';
import { AttachmentItem, TruncateText, HtmlViewerWrapper } from '@metafox/ui';
import { styled, Box } from '@mui/material';
import { isString } from 'lodash';

const name = 'QuotePostMain';

const Root = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  width: '100%',
  padding: '16px',
  borderRadius: theme.shape.borderRadius,
  border: 'solid 1px rgba(85, 85, 85, 0.2)',
  backgroundColor:
    theme.palette.mode === 'dark'
      ? theme.palette.grey[700]
      : theme.palette.grey[100]
}));

const PostContent = styled(HtmlViewerWrapper, { name, slot: 'postContent' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(13),
    lineHeight: 1.33,
    marginTop: theme.spacing(1.5)
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
  justifyContent: 'space-between'
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

export default function QuotePostMain({ item, hideAttachment = false }) {
  const { useGetItem, useGetItems, i18n } = useGlobal();
  const attachments = useGetItems(item?.attachments);
  const { content: description, user: userIdentity } = item;
  let user = useGetItem(userIdentity);

  if (!user && !isString(userIdentity)) {
    user = userIdentity;
  }

  return (
    <Root>
      <Box>
        {user ? (
          <TruncateText color={'text.secondary'} variant="body2" lines={1}>
            {i18n.formatMessage({ id: 'originally_posted_by' })}{' '}
            <Link
              variant="body2"
              color={'text.primary'}
              to={user?.link}
              children={user?.full_name}
              hoverCard={`/user/${user?.id}`}
              sx={{ fontWeight: 'bold', display: 'inline' }}
            />
          </TruncateText>
        ) : null}
      </Box>
      <PostContent>
        <HtmlViewer html={description || ''} />
      </PostContent>
      {!hideAttachment && attachments?.length > 0 && (
        <>
          <AttachmentTitle>
            {i18n.formatMessage({ id: 'attachments' })}
          </AttachmentTitle>
          <Attachment>
            {attachments.map((item: any, index) => (
              <AttachmentItemWrapper key={item?.id.toString()}>
                <AttachmentItem
                  fileName={item.file_name}
                  downloadUrl={item.download_url}
                  isImage={item.is_image}
                  fileSizeText={item.file_size_text}
                  size="mini"
                  image={item?.image}
                  identity={item?._identity}
                  index={index}
                />
              </AttachmentItemWrapper>
            ))}
          </Attachment>
        </>
      )}
    </Root>
  );
}
QuotePostMain.displayName = 'ForumQuotePostMain';
