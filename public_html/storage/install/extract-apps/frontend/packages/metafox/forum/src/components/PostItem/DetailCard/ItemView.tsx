import { Link, useGlobal } from '@metafox/framework';
import React from 'react';
import HtmlViewer from '@metafox/html-viewer';
import {
  AttachmentItem,
  DotSeparator,
  FromNow,
  ItemUserShape,
  UserAvatar,
  ItemView,
  ItemAction,
  HtmlViewerWrapper
} from '@metafox/ui';
import { styled, Box } from '@mui/material';
import LoadingSkeleton from './LoadingSkeleton';

const name = 'PostItemDetailCard';

const PostContent = styled('div', { name, slot: 'postContent' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(15),
    lineHeight: 1.33
  })
);
const AvatarWrapper = styled('div', { name, slot: 'AvatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1.5)
  })
);

const HeaderSubInfo = styled(Box, { name: 'HeaderSubInfo' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  justifyContent: 'center'
}));

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

export default function PostItemDetailCard({
  item,
  identity,
  user,
  state,
  handleAction,
  wrapAs,
  wrapProps
}) {
  const {
    ItemActionMenu,
    useGetItems,
    useGetItem,
    i18n,
    ItemDetailInteraction,
    jsxBackend,
    usePageParams
  } = useGlobal();
  const { post_id } = usePageParams();
  const {
    content: description,
    quote_post: quotePostIdentity,
    quote_user,
    quote_content,
    attachments: attachmentsIdentity
  } = item || {};
  const attachments = useGetItems(attachmentsIdentity);
  const quotePost = useGetItem(quotePostIdentity);
  const ref = React.useRef();

  React.useEffect(() => {
    if (!post_id) return;

    const id = `forum.entities.forum_post.${post_id}`;

    if (identity !== id) return;

    const yOffset = -60;
    const element: HTMLDivElement = ref?.current;

    if (!element) return;

    const y =
      element.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: 'smooth' });
  }, [post_id, identity]);

  if (!item) return null;

  return (
    <ItemView
      ref={ref}
      testid={item.resource_name}
      wrapAs={wrapAs}
      wrapProps={wrapProps}
    >
      <Box sx={{ width: '100%' }}>
        <ItemAction sx={{ position: 'absolute', top: 8, right: 8 }}>
          <ItemActionMenu
            identity={identity}
            icon={'ico-dottedmore-vertical-o'}
            state={state}
            menuName="detailActionMenu"
            handleAction={handleAction}
            size="smaller"
          />
        </ItemAction>
        <Box display="flex">
          <AvatarWrapper>
            <UserAvatar user={user as ItemUserShape} size={48} />
          </AvatarWrapper>
          <HeaderSubInfo>
            <Link
              variant="body1"
              color={'text.primary'}
              to={user.link}
              children={user?.full_name}
              hoverCard={`/user/${user.id}`}
              sx={{ fontWeight: 'bold', display: 'block' }}
            />
            <DotSeparator sx={{ color: 'text.secondary', mt: 0.5 }}>
              <FromNow value={item?.creation_date} shorten />
            </DotSeparator>
          </HeaderSubInfo>
        </Box>
        {quote_content || quotePost ? (
          <Box mt={2}>
            {jsxBackend.render({
              component: 'forum_post.ui.quoteItem',
              props: {
                item: quote_content
                  ? { user: quote_user, content: quote_content }
                  : quotePost
              }
            })}
          </Box>
        ) : null}
        <PostContent>
          <HtmlViewerWrapper>
            <HtmlViewer html={description || ''} />
          </HtmlViewerWrapper>
        </PostContent>
        {attachments?.length > 0 && (
          <>
            <AttachmentTitle>
              {i18n.formatMessage({ id: 'attachments' })}
            </AttachmentTitle>
            <Attachment>
              {attachments.map((item: any, index) => (
                <AttachmentItemWrapper key={item.id.toString()}>
                  <AttachmentItem
                    fileName={item.file_name}
                    downloadUrl={item.download_url}
                    isImage={item.is_image}
                    fileSizeText={item.file_size_text}
                    size="large"
                    image={item?.image}
                    identity={item?._identity}
                    index={index}
                  />
                </AttachmentItemWrapper>
              ))}
            </Attachment>
          </>
        )}
        <ItemDetailInteraction
          identity={identity}
          state={state}
          handleAction={handleAction}
          forceHideCommentList
          borderTop={false}
        />
      </Box>
    </ItemView>
  );
}
PostItemDetailCard.LoadingSkeleton = LoadingSkeleton;
PostItemDetailCard.displayName = 'ForumPostItem(DetailCard)';
