/**
 * @type: block
 * name: blog.block.blogView
 * title: Blog Detail
 * keywords: blog
 * description: Display blog detail.
 * experiment: true
 */

import { BlogDetailViewProps as Props } from '@metafox/blog';
import actionCreators from '@metafox/blog/actions/blogItemActions';
import { BlogDetailViewProps as ItemProps } from '@metafox/blog/types';
import {
  connectItemView,
  connectSubject,
  createBlock,
  Link,
  useGlobal
} from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { Block, BlockContent } from '@metafox/layout';
import {
  AttachmentItem,
  CategoryList,
  DraftFlag,
  FeaturedFlag,
  ItemAction,
  ItemTitle,
  ItemView,
  SponsorFlag,
  HtmlViewerWrapper,
  AuthorInfo
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled, Typography } from '@mui/material';
import React from 'react';

const name = 'BlogDetailView';

const BgCover = styled('div', {
  name,
  slot: 'bgCover',
  shouldForwardProp: prop => prop !== 'isModalView'
})<{ isModalView: boolean }>(({ theme, isModalView }) => ({
  position: 'relative',
  zIndex: 2,
  backgroundRepeat: 'no-repeat',
  backgroundPosition: 'center',
  backgroundSize: 'contain',
  height: 320,
  ...(isModalView && {
    marginLeft: theme.spacing(-2),
    marginRight: theme.spacing(-2)
  }),
  [theme.breakpoints.down('sm')]: {
    height: 179
  }
}));

const BgCoverBlur = styled('div', {
  name,
  slot: 'bgCover',
  shouldForwardProp: prop => prop !== 'isModalView'
})(({ theme }) => ({
  position: 'absolute',
  left: 0,
  right: 0,
  bottom: 0,
  top: 0,
  backgroundRepeat: 'no-repeat',
  backgroundPosition: 'center',
  backgroundSize: 'cover',
  zIndex: 1,
  backgroundColor: theme.palette.background.default,
  filter: 'blur(100px)'
}));
const BlogViewContainer = styled('div', { name, slot: 'blogViewContainer' })(
  ({ theme }) => ({
    width: '100%',
    marginLeft: 'auto',
    marginRight: 'auto',
    padding: theme.spacing(2),
    position: 'relative',
    borderBottomLeftRadius: theme.shape.borderRadius,
    borderBottomRightRadius: theme.shape.borderRadius
  })
);

const BlogContent = styled('div', { name, slot: 'blogContent' })(
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

export function LoadingSkeleton(props) {
  return <ItemView {...props} />;
}

export function DetailView({
  user,
  identity,
  item,
  state,
  actions,
  handleAction,
  isModalView
}: ItemProps) {
  const {
    ItemActionMenu,
    ItemDetailInteraction,
    useGetItems,
    i18n,
    jsxBackend
  } = useGlobal();
  const categories = useGetItems<{ id: number; name: string }>(
    item?.categories
  );

  const attachments = useGetItems(item?.attachments);
  const PendingCard = jsxBackend.get('core.itemView.pendingReviewCard');

  if (!user || !item) return null;

  const cover = getImageSrc(item?.image, 'origin');

  const { tags } = item;

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        {cover ? (
          <Box sx={{ position: 'relative', overflow: 'hidden' }}>
            <BgCover
              isModalView={isModalView}
              style={{ backgroundImage: `url(${cover})` }}
            ></BgCover>
            <BgCoverBlur style={{ backgroundImage: `url(${cover})` }} />
          </Box>
        ) : null}
        {PendingCard && <PendingCard sxWrapper={{ p: 2, pb: 0 }} item={item} />}
        <BlogViewContainer>
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
          <CategoryList data={categories} sx={{ mb: 1, mr: 2 }} />
          <ItemTitle variant="h3" component={'div'} pr={2} showFull>
            <FeaturedFlag variant="itemView" value={item.is_featured} />
            <SponsorFlag
              variant="itemView"
              value={item.is_sponsor}
              item={item}
            />
            <DraftFlag
              value={item.is_draft}
              variant="h3"
              component="span"
              sx={{
                verticalAlign: 'middle',
                fontWeight: 'normal'
              }}
            />
            <Typography
              component="h1"
              variant="h3"
              sx={{
                pr: 2.5,
                display: { sm: 'inline', xs: 'block' },
                mt: { sm: 0, xs: 1 },
                verticalAlign: 'middle'
              }}
            >
              {item?.title}
            </Typography>
          </ItemTitle>
          <AuthorInfo item={item} />
          <BlogContent>
            <HtmlViewerWrapper>
              <HtmlViewer html={item?.text || ''} />
            </HtmlViewerWrapper>
          </BlogContent>
          {tags?.length > 0 ? (
            <Box mt={4} display="flex" flexWrap="wrap">
              {tags.map(tag => (
                <TagItem key={tag}>
                  <Link to={`/blog/search?q=%23${encodeURIComponent(tag)}`}>
                    {tag}
                  </Link>
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
          <ItemDetailInteraction
            identity={identity}
            state={state}
            handleAction={handleAction}
          />
        </BlogViewContainer>
      </BlockContent>
    </Block>
  );
}

DetailView.LoadingSkeleton = LoadingSkeleton;
DetailView.displayName = 'BlogItem_DetailView';

const Enhance = connectSubject(
  connectItemView(DetailView, actionCreators, {
    categories: true,
    attachments: true
  })
);

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    blockLayout: 'Detail - Paper - Radius Bottom'
  }
});
