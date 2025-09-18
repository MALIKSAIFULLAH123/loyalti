import { Link, useGlobal } from '@metafox/framework';
import React from 'react';
import HtmlViewer from '@metafox/html-viewer';
import {
  AttachmentItem,
  FormatDate,
  ItemUserShape,
  UserAvatar,
  ItemView,
  ItemAction,
  PendingFlag,
  ItemSubInfo,
  UserName
} from '@metafox/ui';
import { styled, Box } from '@mui/material';
import LoadingSkeleton from './LoadingSkeleton';

const name = 'PostItemDetailCard';

const PostContent = styled('div', { name, slot: 'postContent' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(15),
    lineHeight: 1.33,
    marginTop: theme.spacing(2),
    '& p + p': {
      marginBottom: theme.spacing(2.5)
    }
  })
);
const AvatarWrapper = styled('div', { name, slot: 'AvatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1.5)
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

const ProfileLink = styled(UserName, { name, slot: 'profileLink' })(
  ({ theme }) => ({
    color: theme.palette.text.primary,
    fontSize: theme.mixins.pxToRem(13),
    '&:hover': {
      textDecoration: 'underline'
    }
  })
);

// const SubInfoStyled = styled('div', { name, slot: 'subInfoStyled' })(
//   ({ theme }) => ({
//     display: 'flex',
//     alignItems: 'center',
//     marginRight: theme.spacing(1),
//     flexFlow: 'wrap',
//     [theme.breakpoints.down('sm')]: {
//       flexDirection: 'column',
//       alignItems: 'flex-start'
//     }
//   })
// );

export default function PostItemDetailCard({
  item,
  identity,
  itemProps,
  user,
  state,
  handleAction,
  wrapAs,
  wrapProps
}) {
  const { ItemActionMenu, useGetItem, useGetItems, i18n, jsxBackend } =
    useGlobal();
  const attachments = useGetItems(item?.attachments);

  const {
    content: description,
    thread: threadIdentity,
    quote_post: quotePostIdentity,
    quote_user,
    quote_content
  } = item;
  const quotePost = useGetItem(quotePostIdentity);
  const thread = useGetItem(threadIdentity);
  const to = thread ? `/forum/thread/${thread?.id}` : '';

  if (!thread) return null;

  return (
    <ItemView testid={item.resource_name} wrapAs={wrapAs} wrapProps={wrapProps}>
      <Box sx={{ width: '100%' }}>
        {itemProps.showActionMenu ? (
          <ItemAction sx={{ position: 'absolute', top: 8, right: 8 }}>
            <ItemActionMenu
              identity={identity}
              icon={'ico-dottedmore-vertical-o'}
              state={state}
              handleAction={handleAction}
            />
          </ItemAction>
        ) : null}
        <Box mt={2} display="flex">
          <AvatarWrapper>
            <UserAvatar user={user as ItemUserShape} size={48} />
          </AvatarWrapper>
          <Box>
            <PendingFlag variant="itemView" value={!item.is_approved} />

            <ItemSubInfo sx={{ color: 'text.secondary', mt: 1 }}>
              <ProfileLink to={user?.link} user={user} />
              <FormatDate
                data-testid="publishedDate"
                value={item?.creation_date}
                format="LL"
              />
            </ItemSubInfo>
          </Box>
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
          <HtmlViewer html={description || ''} />
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
                    identity={item?._identity}
                    index={index}
                  />
                </AttachmentItemWrapper>
              ))}
            </Attachment>
          </>
        )}
        <Box sx={{ color: 'text.secondary' }}>
          <span>{i18n.formatMessage({ id: 'parent_thread' })}: </span>
          <Link color={'primary'} to={to}>
            {thread.title}
          </Link>
        </Box>
      </Box>
    </ItemView>
  );
}
PostItemDetailCard.LoadingSkeleton = LoadingSkeleton;
PostItemDetailCard.displayName = 'ForumPostItem(PostItemDetailCard)';
