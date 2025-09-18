import React from 'react';
import { formatGeneralMsg } from '@metafox/chatplus/services/formatTextMsg';
import { filterImageAttachment } from '@metafox/chatplus/utils';
import { useGlobal } from '@metafox/framework';
import { LineIcon, TruncateText } from '@metafox/ui';
import { Box, styled, Typography } from '@mui/material';
import MsgAttachmentMedia from '../MsgAttachments/MsgAttachmentMedia';
import { isEmpty } from 'lodash';
import { JUMP_MSG_ACTION } from '@metafox/chatplus/constants';

interface Props {
  dataQuote: any;
  isOwner?: boolean;
  rid: string;
}

const name = 'MsgQuote';

const UIMsgAttachment = styled('div', {
  name,
  slot: 'UIMsgAttachment',
  shouldForwardProp: prop =>
    prop !== 'isOwner' &&
    prop !== 'isAudio' &&
    prop !== 'isTypeFile' &&
    prop !== 'isOther' &&
    prop !== 'msgType'
})<{
  isOwner?: boolean;
  isAudio?: boolean;
  isTypeFile?: boolean;
  isOther?: boolean;
  msgType?: string;
}>(({ theme, isOwner, isAudio, isTypeFile, isOther, msgType }) => ({
  opacity: '0.8',
  marginBottom: theme.spacing(-2),
  marginLeft: theme.spacing(1),
  marginRight: 0,
  marginTop: theme.spacing(0.25),
  paddingBottom: theme.spacing(3),
  maxWidth: 'calc(100% - 8px)',
  display: 'flex',
  flexDirection: 'row',
  overflow: 'hidden',
  border: theme.mixins.border('secondary'),
  borderRadius: theme.spacing(1),
  minHeight: '70px',
  cursor: 'pointer',
  ...((isAudio ||
    msgType === 'message_pinned' ||
    msgType === 'message_unpinned' ||
    isOther) && {
    '& a': {
      color: theme.palette.text.primary,
      textDecoration: 'underline',
      cursor: 'pointer'
    },
    ...(isOwner && {
      color: theme.palette.text.secondary,
      marginRight: theme.spacing(1),
      marginLeft: 0
    })
  })
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
  }
}));

const AuthorNameStyled = styled(Typography)(({ theme }) => ({
  color:
    theme.palette.mode === 'light'
      ? theme.palette.grey['A200']
      : theme.palette.text.primary
}));
const UIMsgAttachmentFlex = styled('div', {
  name,
  slot: 'MsgAttachmentFlex',
  shouldForwardProp: props => props !== 'isAttachment'
})<{ isAttachment?: boolean }>(({ theme, isAttachment }) => ({
  textAlign: 'start',
  ...(isAttachment && {
    maxWidth: '300px'
  })
}));

const UIMsgAttachmentText = styled(TruncateText, {
  name,
  slot: 'uiMsgAttachmentText'
})(({ theme }) => ({
  color: theme.palette.text.secondary
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
    overflow: 'hidden',
    margin: theme.spacing(1, 1, 0, 0)
  })
}));
const UIMsgAttachmentLink = styled(TruncateText, {
  name,
  slot: 'uiMsgAttachmentLink'
})(({ theme }) => ({
  cursor: 'pointer',
  '& .ico': {
    fontSize: theme.mixins.pxToRem(12),
    marginRight: theme.spacing(0.5)
  }
}));

const DividerStyled = styled('div')(({ theme }) => ({
  width: theme.spacing(0.5),
  height: '100%',
  backgroundColor: theme.palette.grey['100']
}));

const DividerWrapperStyled = styled('div')(({ theme }) => ({
  width: theme.spacing(3),
  minWidth: theme.spacing(3),
  marginTop: theme.spacing(1.5),
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center'
}));

const IconPhotoStyled = styled(LineIcon)(({ theme }) => ({
  marginRight: theme.spacing(0.5),
  display: 'flex',
  alignItems: 'center'
}));

export default function MsgQuote({
  dataQuote,
  isOwner,
  rid,
  scrollMessage
}: Props): JSX.Element {
  const { i18n, dispatch } = useGlobal();

  if (!dataQuote) return null;

  const {
    mentions = [],
    author_real_name,
    author_name,
    text,
    textRaw,
    audio_url,
    attachments,
    type,
    msgType,
    t
  } = dataQuote;

  const isTypeFile = type === 'file';

  const textMsg = textRaw || text;
  const textQuote = textMsg
    ? formatGeneralMsg(textMsg, { mentions, onlyShowText: true })
    : '';

  const { count: countImage } = filterImageAttachment(attachments);

  const handleClick = () => {
    if (t === 'rm') return;

    dispatch({
      type: JUMP_MSG_ACTION,
      payload: { roomId: rid, mid: dataQuote?.message_id, mode: 'quote' },
      meta: { onSuccess: scrollMessage }
    });
  };

  return (
    <UIMsgAttachment
      isOwner={isOwner}
      isAudio={!!audio_url}
      isTypeFile={!!isTypeFile}
      isOther={!audio_url && !isTypeFile}
      msgType={msgType}
      onClick={handleClick}
    >
      <DividerWrapperStyled>
        <DividerStyled />
      </DividerWrapperStyled>
      <UIMsgAttachmentInfoWrapper
        msgType={msgType}
        isOther={!audio_url && !isTypeFile}
      >
        {author_name ? (
          <UIMsgAttachmentAuthor>
            <AuthorNameStyled component="h2" variant="h5">
              {author_real_name || author_name}
            </AuthorNameStyled>
          </UIMsgAttachmentAuthor>
        ) : null}
        <UIMsgAttachmentFlex isAttachment={attachments?.length}>
          {t === 'rm' ? (
            <UIMsgAttachmentTextDelete>
              {i18n.formatMessage({ id: 'message_was_deleted' })}
            </UIMsgAttachmentTextDelete>
          ) : null}
          {!isEmpty(textQuote) && t !== 'rm' ? (
            <UIMsgAttachmentText
              lines={1}
              dangerouslySetInnerHTML={{
                __html: textQuote
              }}
            />
          ) : null}
          {t !== 'rm' && attachments && attachments.length ? (
            <Box mt={0.5}>
              {countImage > 1 ? (
                <UIMsgAttachmentText lines={1}>
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    <IconPhotoStyled icon={'ico-photos-alt-o'} />
                    {i18n.formatMessage(
                      { id: 'total_photo' },
                      { value: countImage }
                    )}
                  </Box>
                </UIMsgAttachmentText>
              ) : (
                <>
                  {attachments.map((item, i) => (
                    <React.Fragment key={`k${i}`}>
                      {item.video_url || item.image_url ? (
                        <MsgAttachmentMedia
                          {...item}
                          key={`k${i}`}
                          msgType={msgType}
                          isOther={!audio_url && !isTypeFile}
                          allowOpenPreview={false}
                        />
                      ) : item.audio_url ? (
                        <UIMsgAttachmentText lines={1}>
                          {item?.title ||
                            i18n.formatMessage({ id: 'file_attachment' })}
                        </UIMsgAttachmentText>
                      ) : (
                        <div>
                          {item.title_link ? (
                            <UIMsgAttachmentLink lines={1}>
                              <div style={{ display: 'inline' }}>
                                <LineIcon icon="ico-paperclip-alt" />
                                {item.title}
                              </div>
                            </UIMsgAttachmentLink>
                          ) : null}
                        </div>
                      )}
                    </React.Fragment>
                  ))}
                </>
              )}
            </Box>
          ) : null}
        </UIMsgAttachmentFlex>
      </UIMsgAttachmentInfoWrapper>
    </UIMsgAttachment>
  );
}
