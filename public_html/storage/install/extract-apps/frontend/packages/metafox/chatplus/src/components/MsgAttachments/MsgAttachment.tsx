import { useChatUserItem } from '@metafox/chatplus/hooks';
import formatTextMsg from '@metafox/chatplus/services/formatTextMsg';
import { filterImageAttachment, triggerClick } from '@metafox/chatplus/utils';
import { useGlobal } from '@metafox/framework';
import { LineIcon, TruncateText } from '@metafox/ui';
import { Box, styled } from '@mui/material';
import React from 'react';
import MsgAvatar from '../Messages/MsgAvatar';
import MsgAttachmentMedia from './MsgAttachmentMedia';
import MsgAttachmentMultiMedia from './MsgAttachmentMultiMedia';

const name = 'MsgAttachment';

const UIMsgAttachment = styled('div', {
  name,
  slot: 'UIMsgAttachment',
  shouldForwardProp: prop =>
    prop !== 'isOwner' &&
    prop !== 'isAudio' &&
    prop !== 'isTypeFile' &&
    prop !== 'isOther' &&
    prop !== 'totalImage' &&
    prop !== 'msgType'
})<{
  isOwner?: boolean;
  isAudio?: boolean;
  isTypeFile?: boolean;
  isOther?: boolean;
  msgType?: string;
  totalImage?: number;
}>(({ theme, isOwner, isAudio, isTypeFile, isOther, msgType, totalImage }) => ({
  zIndex: 2,
  maxWidth: '100%',
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-start',
  paddingBottom: theme.spacing(0.5),
  marginTop: theme.spacing(0.25),
  overflow: 'hidden',
  ...((isTypeFile || totalImage) && { width: '100%' }),
  ...((isAudio ||
    msgType === 'message_pinned' ||
    msgType === 'message_unpinned' ||
    isOther) && {
    backgroundColor: theme.palette.grey['100'],
    ...(theme.palette.mode === 'dark' && {
      backgroundColor: theme.palette.grey['600']
    }),
    padding: theme.spacing(0.5, 0.75),
    borderRadius: theme.spacing(1.25),
    '& a': {
      color: isOwner ? '#fff' : theme.palette.text.primary,
      textDecoration: 'underline',
      cursor: 'pointer'
    },
    ...(isOwner && {
      backgroundColor: theme.palette.primary.main,
      color: '#fff !important'
    })
  }),
  ...(isOwner && {
    alignItems: 'flex-end'
  }),
  ...(isAudio && { width: '185px', paddingTop: theme.spacing(1.5) })
}));
const TitleLinkStyled = styled('div', {
  name,
  slot: 'TitleLinkStyled',
  shouldForwardProp: prop =>
    prop !== 'isOwner' &&
    prop !== 'isVideo' &&
    prop !== 'isImage' &&
    prop !== 'isAudio' &&
    prop !== 'isTypeFile'
})<{
  isOwner?: boolean;
  isVideo?: boolean;
  isImage?: boolean;
  isAudio?: boolean;
  isTypeFile?: boolean;
}>(({ theme, isOwner, isVideo, isImage, isAudio, isTypeFile }) => ({
  borderRadius: theme.spacing(1),
  fontSize: theme.spacing(1.75),
  padding: theme.spacing(1),
  overflow: 'hidden',
  whiteSpace: 'nowrap',
  textOverflow: 'ellipsis',
  display: 'inline-block',
  maxWidth: '100%',
  wordBreak: 'break-word',
  wordWrap: 'break-word',
  backgroundColor: theme.palette.grey['100'],
  ...(theme.palette.mode === 'dark' && {
    backgroundColor: theme.palette.grey['600']
  }),
  ...(isOwner && {
    backgroundColor: theme.palette.primary.main,
    color: '#fff'
  }),

  ...((isVideo || isImage) && { display: 'none' }),
  ...(isAudio && { width: '300px' }),
  cursor: 'pointer',
  '& .ico': {
    marginRight: theme.spacing(1)
  }
}));

const DescriptionStyled = styled('div', {
  name,
  slot: 'DescriptionStyled',
  shouldForwardProp: prop => prop !== 'isOwner' && prop !== 'isVideo'
})<{ isOwner?: boolean; isVideo?: boolean }>(({ theme, isOwner, isVideo }) => ({
  backgroundColor: theme.palette.grey['100'],
  ...(theme.palette.mode === 'dark' && {
    backgroundColor: theme.palette.grey['600']
  }),
  borderRadius: theme.spacing(1),
  fontSize: theme.spacing(1.75),
  padding: theme.spacing(1),
  display: 'inline-block',
  ...(isOwner && { backgroundColor: theme.palette.primary.main, color: '#fff' })
}));

const UIMsgAttachmentAuthor = styled('div', {
  name,
  slot: 'uiMsgAttachmentAuthor'
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'flex-start',
  alignItems: 'center',
  '& .MuiAvatar-root': {
    fontSize: `${theme.spacing(1)} !important`
  },
  strong: {
    padding: theme.spacing(0, 0.5, 0, 0.25)
  }
}));
const UIMsgAttachmentFlex = styled('div', {
  name,
  slot: 'uiMsgAttachmentFlex'
})(({ theme }) => ({
  textAlign: 'start'
}));
const UIMsgAttachmentText = styled('div', {
  name,
  slot: 'uiMsgAttachmentText'
})(({ theme }) => ({
  margin: theme.spacing(0.5, 0)
}));
const UIMsgAttachmentTextDelete = styled('div', {
  name,
  slot: 'UIMsgAttachmentTextDelete'
})(({ theme }) => ({
  margin: theme.spacing(0.5),
  marginLeft: 0,
  fontStyle: 'italic'
}));
const UIMsgAttachmentInfoWrapper = styled('div', {
  name,
  slot: 'uiMsgAttachmentInfoWrapper',
  shouldForwardProp: prop => prop !== 'msgType' && prop !== 'isOther'
})<{ msgType?: string; isOther?: boolean }>(({ theme, msgType, isOther }) => ({
  ...((msgType === 'message_pinned' ||
    msgType === 'message_unpinned' ||
    isOther) && {
    borderLeft: '2px solid #a2a2a2',
    paddingLeft: theme.spacing(1),
    width: '100%'
  })
}));
const UIMsgAttachmentLink = styled(TruncateText, {
  name,
  slot: 'uiMsgAttachmentLink'
})(({ theme }) => ({
  cursor: 'pointer',
  '& .ico': {
    marginRight: theme.spacing(1)
  }
}));

interface MsgAttachmentProps {
  mentions: any;
  title: string;
  author_real_name: string;
  author_name: string;
  author_id?: string;
  text: string;
  image_url: string;
  image_dimensions?: { width: number; height: number };
  audio_url: string;
  video_url: string;
  video_type: string;
  title_link: string;
  description: string;
  video_thumb_url: string;
  attachments: any;
  type: string;
  layout: string;
  isOwner?: boolean;
  msgType?: 'message_pinned' | 'message_unpinned' | string;
  t?: any;
  video_width?: number;
  video_height?: number;
  audio_duration?: number;
}

export default function MsgAttachment({
  mentions = [],
  title,
  author_real_name,
  author_name,
  author_id,
  text,
  image_url,
  image_dimensions,
  audio_url,
  audio_duration,
  video_url,
  video_type,
  title_link,
  description,
  video_thumb_url,
  attachments,
  type,
  layout,
  isOwner,
  msgType,
  t,
  video_width,
  video_height
}: MsgAttachmentProps) {
  const { chatplus, i18n } = useGlobal();
  const isTypeFile = type === 'file';
  const { count: countImage, data: dataChildImagesAttachment } =
    filterImageAttachment(attachments);

  const userInfo = useChatUserItem(author_id);

  return (
    <UIMsgAttachment
      isOwner={isOwner}
      isAudio={!!audio_url}
      isTypeFile={!!isTypeFile}
      isOther={!audio_url && !isTypeFile}
      msgType={msgType}
      totalImage={countImage}
    >
      <UIMsgAttachmentInfoWrapper
        msgType={msgType}
        isOther={!audio_url && !isTypeFile}
      >
        {author_name ? (
          <UIMsgAttachmentAuthor>
            <MsgAvatar
              size={16}
              name={userInfo?.name || author_name}
              username={userInfo?.username || author_name}
              avatarETag={userInfo?.avatarETag}
            />
            <strong>{author_real_name || author_name}</strong>
          </UIMsgAttachmentAuthor>
        ) : null}
        <UIMsgAttachmentFlex>
          {t === 'rm' ? (
            <UIMsgAttachmentTextDelete>
              {i18n.formatMessage({ id: 'message_was_deleted' })}
            </UIMsgAttachmentTextDelete>
          ) : null}
          {text && t !== 'rm' ? (
            <UIMsgAttachmentText
              dangerouslySetInnerHTML={{
                __html: formatTextMsg(text, { mentions })
              }}
            />
          ) : null}
          {attachments && attachments.length ? (
            <Box mt={1}>
              {countImage > 1 ? (
                <MsgAttachmentMultiMedia
                  mediaItems={dataChildImagesAttachment}
                  isOwner={isOwner}
                  msgType={msgType}
                  isOther={!audio_url && !isTypeFile}
                />
              ) : (
                <>
                  {attachments.map((item, i) => (
                    <React.Fragment key={`k${i}`}>
                      {item.video_url || item.image_url || item.audio_url ? (
                        <MsgAttachmentMedia
                          {...item}
                          key={`k${i}`}
                          msgType={msgType}
                          isOther={!audio_url && !isTypeFile}
                        />
                      ) : (
                        <>
                          {item.title_link ? (
                            <UIMsgAttachmentLink lines={1}>
                              <div
                                style={{ display: 'inline' }}
                                onClick={() =>
                                  triggerClick(
                                    chatplus.sanitizeRemoteFileUrl(
                                      item.title_link
                                    ),
                                    false,
                                    true
                                  )
                                }
                              >
                                <LineIcon icon="ico-arrow-down-circle" />
                                {item.title}
                              </div>
                            </UIMsgAttachmentLink>
                          ) : null}
                        </>
                      )}
                    </React.Fragment>
                  ))}
                </>
              )}
            </Box>
          ) : null}
        </UIMsgAttachmentFlex>
      </UIMsgAttachmentInfoWrapper>
      <MsgAttachmentMedia
        image_url={image_url}
        video_url={video_url}
        audio_url={audio_url}
        audio_duration={audio_duration}
        title={title}
        video_type={video_type}
        video_thumb_url={video_thumb_url}
        layout={layout}
        image_dimensions={image_dimensions}
        isOwner={isOwner}
        video_width={video_width}
        video_height={video_height}
      />
      {title_link ? (
        <TitleLinkStyled
          isOwner={isOwner}
          isVideo={!!video_url}
          isImage={!!image_url}
          isAudio={!!audio_url}
          isTypeFile={!!isTypeFile}
        >
          <TruncateText
            lines={1}
            onClick={() =>
              triggerClick(
                chatplus.sanitizeRemoteFileUrl(title_link),
                false,
                true
              )
            }
          >
            <LineIcon icon="ico-arrow-down-circle" />
            {title}
          </TruncateText>
        </TitleLinkStyled>
      ) : null}
      {description ? (
        <DescriptionStyled
          isOwner={isOwner}
          isVideo={!!video_url}
          isImage={!!image_url}
          isAudio={!!audio_url}
          isTypeFile={!!isTypeFile}
        >
          {description}
        </DescriptionStyled>
      ) : null}
    </UIMsgAttachment>
  );
}
