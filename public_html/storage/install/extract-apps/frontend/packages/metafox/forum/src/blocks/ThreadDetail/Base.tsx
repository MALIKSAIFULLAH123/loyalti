import { ThreadDetailViewProps as ItemProps } from '@metafox/forum/types';
import { Link, useGlobal, useResourceAction } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { Block, BlockContent } from '@metafox/layout';
import {
  AttachmentItem,
  DotSeparator,
  FeaturedFlag,
  FormatDate,
  ItemAction,
  ItemTitle,
  SponsorFlag,
  LineIcon,
  AuthorInfo,
  HtmlViewerWrapper
} from '@metafox/ui';
import {
  Box,
  styled,
  Typography,
  useMediaQuery,
  useTheme,
  Chip
} from '@mui/material';
import React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import { APP_FORUM, RESOURCE_FORUM_THREAD } from '@metafox/forum/constants';

const name = 'ThreadDetailView';

export type Props = ItemProps;

const ContentWrapper = styled('div', { name, slot: 'ContentWrapper' })(
  ({ theme }) => ({
    backgroundColor: theme.mixins.backgroundColor('paper'),
    borderRadius: theme.shape.borderRadius
  })
);

const ThreadViewContainer = styled('div', {
  name,
  slot: 'threadViewContainer'
})(({ theme }) => ({
  width: '100%',
  marginLeft: 'auto',
  marginRight: 'auto',
  padding: `${theme.spacing(2)} ${theme.spacing(2)} 0 ${theme.spacing(2)}`,
  position: 'relative',
  borderBottomLeftRadius: theme.shape.borderRadius,
  borderBottomRightRadius: theme.shape.borderRadius
}));

const ThreadContent = styled('div', { name, slot: 'threadContent' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(15),
    lineHeight: 1.33
  })
);
const TagItem = styled('div', { 
  name, 
  slot: 'tagItem',
  overridesResolver(props, styles) {
   return [styles.tagItem];
  }
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(13),
  fontWeight: theme.typography.fontWeightBold,
  borderRadius: theme.shape.borderRadius / 2,
  background:
    theme.palette.mode === 'light'
      ? theme.palette.background.default
      : theme.palette.action.hover,
  marginRight: theme.spacing(1),
  marginBottom: theme.spacing(1),
  padding: theme.spacing(0, 1.5),
  height: theme.spacing(3),
  lineHeight: theme.spacing(3),
  display: 'block',
  color: theme.palette.mode === 'light' ? '#121212' : '#fff'
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
  justifyContent: 'space-between',
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column'
  }
}));
const AttachmentItemWrapper = styled('div', {
  name,
  slot: 'attachmentItemWrapper'
})(({ theme }) => ({
  marginTop: theme.spacing(2),
  flexGrow: 0,
  flexShrink: 0,
  flexBasis: 'calc(50% - 8px)',
  minWidth: 300,
  maxWidth: '100%'
}));

const IconTitle = styled(LineIcon, { name, slot: 'IconTitle' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1),
    color: theme.palette.success.main,
    display: 'inline-block',
    transform: 'translateY(2px)'
  })
);

export default function DetailView({
  user,
  identity,
  item,
  state,
  actions,
  handleAction
}: ItemProps) {
  const {
    ItemActionMenu,
    ItemDetailInteraction,
    useGetItems,
    useGetItem,
    i18n,
    jsxBackend
  } = useGlobal();
  const [isShowPost, setToggleShowPost] = React.useState(true);
  const attachments = useGetItems(item?.attachments);
  const itemAttachItem = useGetItem(item?.item);
  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));
  const dataSourceCommentStatistic = useResourceAction(
    APP_FORUM,
    RESOURCE_FORUM_THREAD,
    'viewPosters'
  );

  const PendingCard = jsxBackend.get('core.itemView.pendingReviewCard');
  const PollView = jsxBackend.get('poll.embedItem.insideFeedItem');
  const PostListing = jsxBackend.get('forum_post.block.detailListingBlock');
  const PostForm = jsxBackend.get('forum_post.block.addForm');

  if (!user || !item) return null;

  const { tags, description, item: itemAttach, is_closed, is_wiki } = item;
  let iconMedia = '';

  if (is_wiki) {
    iconMedia = 'ico-file-word';
  }

  const pollIdentity =
    itemAttach && itemAttach.startsWith('poll.') ? itemAttach : null;

  const handleActionCommentStatistic = () => {
    setToggleShowPost(prev => !prev);
  };

  return (
    <>
      <Block testid={`detailview ${item.resource_name}`}>
        <BlockContent>
          <ContentWrapper>
            {PendingCard && item?.is_pending ? (
              <PendingCard sxWrapper={{ p: 2 }} item={{ ...item }} />
            ) : null}
            <ThreadViewContainer>
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
              <ItemTitle variant="h3" component={'div'} my={0} showFull>
                {is_closed && (
                  <Chip
                    size="small"
                    label={i18n.formatMessage({ id: 'closed' })}
                    variant="filled"
                    sx={{
                      mr: 1,
                      mb: { sm: 0, xs: 1 },
                      color: 'default.contrastText',
                      backgroundColor: theme.palette.mode === 'dark'
                      ? theme.palette.grey[600]
                      : theme.palette.text.secondary,
                      fontSize: '13px'
                    }}
                  />
                )}
                {iconMedia && <IconTitle icon={iconMedia} />}
                <FeaturedFlag variant="itemView" value={item.is_featured} />
                <SponsorFlag
                  variant="itemView"
                  value={item.is_sponsor}
                  item={item}
                />
                <Typography
                  component="h1"
                  variant="h3"
                  sx={{
                    pr: 2.5,
                    display: { sm: 'inline', xs: 'block' },
                    verticalAlign: 'middle'
                  }}
                >
                  {item?.title}
                </Typography>
              </ItemTitle>
              <AuthorInfo
                item={item}
                statisticDisplay={false}
                privacyDisplay={false}
              />
              <ThreadContent>
                <HtmlViewerWrapper>
                  <HtmlViewer html={description || ''} />
                </HtmlViewerWrapper>
                {item?.modification_date ? (
                  <DotSeparator
                    sx={{ color: 'text.secondary', mt: 1, fontStyle: 'italic' }}
                  >
                    <FormatDate
                      data-testid="modifyDate"
                      value={item?.modification_date}
                      format="LL"
                      phrase="last_update_on_time"
                    />
                  </DotSeparator>
                ) : null}
              </ThreadContent>
              {PollView && pollIdentity && !itemAttachItem?.error ? (
                <Box mt={4}>
                  <PollView identity={pollIdentity} isShowPendingCard />
                </Box>
              ) : null}
              {tags?.length > 0 ? (
                <Box mt={4} display="flex" flexWrap="wrap">
                  {tags.map(tag => (
                    <TagItem key={tag}>
                      <Link to={`/forum/search?q=%23${tag}`}>{tag}</Link>
                    </TagItem>
                  ))}
                </Box>
              ) : null}
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
                          size={isSmallScreen ? 'mini' : 'large'}
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
                messageCommentStatistic={'total_reply'}
                dataSourceCommentStatistic={dataSourceCommentStatistic}
                forceHideCommentList
                handleActionCommentStatistic={handleActionCommentStatistic}
              />
            </ThreadViewContainer>
          </ContentWrapper>
        </BlockContent>
      </Block>
      <Box
        sx={{ borderRadius: 1, overflow: 'hidden' }}
        className={!isShowPost && 'srOnly'}
      >
        <PostListing />
        <PostForm blockLayout="Forum Post Form Detail Thread" />
      </Box>
    </>
  );
}

DetailView.LoadingSkeleton = LoadingSkeleton;
DetailView.displayName = 'ThreadItem_DetailView';
