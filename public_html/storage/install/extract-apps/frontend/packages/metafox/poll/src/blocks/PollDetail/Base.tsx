import { useGlobal } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { Block, BlockContent } from '@metafox/layout';
import { PollDetailViewProps } from '@metafox/poll/types';
import {
  AttachmentItem,
  FeaturedFlag,
  ItemTitle,
  RichTextViewMore,
  SponsorFlag,
  HtmlViewerWrapper,
  AuthorInfo
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled, Typography } from '@mui/material';
import React from 'react';
import PollVoteForm from './PollVoteForm';

const name = 'PollDetail';
const ContentWrapper = styled('div', { name, slot: 'ContentWrapper' })(
  ({ theme }) => ({
    position: 'relative'
  })
);

const MessageWrapper = styled('div', { name, slot: 'MessageWrapper' })(
  ({ theme }) => ({
    borderRadius: theme.shape.borderRadius,
    height: theme.spacing(6),
    width: 'auto',
    backgroundColor: theme.palette.action.hover,
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: theme.spacing(0, 2),
    marginBottom: theme.spacing(2)
  })
);

const BgCover = styled('div', {
  name,
  slot: 'bgCover'
})(({ theme }) => ({
  position: 'relative',
  zIndex: 2,
  backgroundRepeat: 'no-repeat',
  backgroundPosition: 'center',
  backgroundSize: 'contain',
  height: 320
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

const ViewContainer = styled(Box, { name: 'ViewContainer' })(({ theme }) => ({
  width: '100%',
  padding: theme.spacing(2),
  position: 'relative'
}));

const AttachmentTitle = styled(Box, { name: 'AttachmentTitle' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(18),
    marginTop: theme.spacing(4),
    color: theme.palette.text.secondary,
    fontWeight: theme.typography.fontWeightBold
  })
);

const Attachment = styled(Box, { name: 'Attachment' })(({ theme }) => ({
  width: '100%',
  display: 'flex',
  flexWrap: 'wrap',
  justifyContent: 'space-between',
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column'
  }
}));

const AttachmentItemWrapper = styled(Box, { name: 'AttachmentItemWrapper' })(
  ({ theme }) => ({
    marginTop: theme.spacing(2),
    flexGrow: 0,
    flexShrink: 0,
    flexBasis: 'calc(50% - 8px)',
    minWidth: 300,
    maxWidth: '100%'
  })
);

const VoteForm = styled(Box, { name: 'VoteForm' })(({ theme }) => ({
  marginTop: theme.spacing(2),
  [theme.breakpoints.up('sm')]: {
    maxWidth: 450
  }
}));

const ItemContent = styled(Box, { name: 'ItemContent' })(({ theme }) => ({
  fontSize: 15,
  lineHeight: 1.33,
  '& p + p': {
    marginBottom: theme.spacing(2.5)
  },
  '& p:last-child': {
    marginBottom: 0
  }
}));

const ActionMenu = styled(Box, { name: 'ActionMenu' })(({ theme }) => ({
  width: 32,
  height: 32,
  [theme.breakpoints.down('sm')]: {
    position: 'absolute',
    top: theme.spacing(-1),
    right: theme.spacing(-1)
  },
  '& .ico': {
    color: theme.palette.text.secondary,
    fontSize: 13
  },
  '& button:hover': {
    backgroundColor: theme.palette.action.selected
  }
}));

export default function PollDetail({
  item,
  user,
  attachments,
  actions,
  answers,
  identity,
  handleAction,
  state
}: PollDetailViewProps) {
  const {
    ItemActionMenu,
    ItemDetailInteraction,
    i18n,
    jsxBackend,
    useSession
  } = useGlobal();

  const { user: authUser } = useSession();

  const isOwnerItem = authUser?.id === user?.id;

  const PendingCard = jsxBackend.get('core.itemView.pendingReviewCard');

  if (!item || !user) return null;

  const {
    item_id,
    is_user_voted,
    is_multiple,
    public_vote,
    is_featured,
    is_sponsor,
    is_pending,
    is_closed,
    extra
  } = item;

  const cover = getImageSrc(item?.image, 'origin');

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <ContentWrapper>
          {cover ? (
            <Box sx={{ position: 'relative', overflow: 'hidden' }}>
              <BgCover style={{ backgroundImage: `url(${cover})` }}></BgCover>
              <BgCoverBlur style={{ backgroundImage: `url(${cover})` }} />
            </Box>
          ) : null}
          <ViewContainer>
            {PendingCard && <PendingCard sxWrapper={{ mb: 1 }} item={item} />}
            {is_closed && (
              <MessageWrapper>
                <Typography variant="h5" color="text.hint">
                  {i18n.formatMessage({ id: 'voting_for_the_poll_was_closed' })}
                </Typography>
              </MessageWrapper>
            )}
            <ContentWrapper>
              <Box display="flex">
                <ItemTitle
                  variant="h3"
                  component={'div'}
                  showFull
                  sx={{ flex: 1 }}
                >
                  <FeaturedFlag variant="itemView" value={is_featured} />
                  <SponsorFlag
                    variant="itemView"
                    value={is_sponsor}
                    item={item}
                  />
                  <Typography
                    component="h1"
                    variant="h3"
                    sx={{
                      display: { sm: 'inline', xs: 'block' },
                      mt: { sm: 0, xs: 1 },
                      verticalAlign: 'middle'
                    }}
                  >
                    {item?.question}
                  </Typography>
                </ItemTitle>
                <ActionMenu>
                  <ItemActionMenu
                    menuName="detailActionMenu"
                    identity={identity}
                    icon={'ico-dottedmore-vertical-o'}
                    state={state}
                    handleAction={handleAction}
                  />
                </ActionMenu>
              </Box>

              <AuthorInfo item={item} />
              {item?.text && (
                <ItemContent>
                  <RichTextViewMore maxHeight="300px">
                    <HtmlViewerWrapper>
                      <HtmlViewer html={item.text || ''} />
                    </HtmlViewerWrapper>
                  </RichTextViewMore>
                </ItemContent>
              )}
              <VoteForm data-testid="voteFormPoll">
                <PollVoteForm
                  isMultiple={is_multiple}
                  isVoted={is_user_voted}
                  pollId={item_id}
                  answers={answers}
                  statistic={item.statistic}
                  closeTime={item.close_time}
                  publicVote={isOwnerItem ? isOwnerItem : public_vote}
                  identity={identity}
                  isPending={is_pending}
                  isClosed={is_closed}
                  canVoteAgain={extra.can_change_vote}
                  canVote={extra.can_vote}
                  canViewResult={extra.can_view_result}
                  canViewResultAfter={extra.can_view_result_after_vote}
                  canViewResultBefore={extra.can_view_result_before_vote}
                />
              </VoteForm>
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
                          image={item?.image}
                          size="large"
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
            </ContentWrapper>
          </ViewContainer>
        </ContentWrapper>
      </BlockContent>
    </Block>
  );
}
