import formatTextMsg from '@metafox/chat/services/formatTextMsg';
import { useGlobal } from '@metafox/framework';
import { UserItemShape } from '@metafox/user';
import { Box, CircularProgress, styled, Typography } from '@mui/material';
import React from 'react';
import { MsgItemShape } from '@metafox/chat/types';
import { LineIcon, TruncateText } from '@metafox/ui';
import { useCheckImageQuote } from '../MsgAttachments/MsgAttachments';
import MsgAttachment from '../MsgAttachments/MsgAttachment';
import { isEmpty } from 'lodash';
import { convertTypeMessage } from '@metafox/chat/utils';
import { useChatRoom, useChatRoomMsgDelete } from '@metafox/chat/hooks';
import { CHAT_JUMP_MSG_ACTION } from '@metafox/chat/constants';
import { alpha } from '@mui/system/colorManipulator';

const name = 'MsgQuote';

const RootMsg = styled('div', {
  name,
  slot: 'RootMsg',
  shouldForwardProp: prop => prop !== 'isOwner' && prop !== 'isSearch'
})<{
  isOwner?: boolean;
  isSearch?: boolean;
}>(({ theme, isOwner, isSearch }) => ({
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
  position: 'relative',
  ...(!isSearch && {
    cursor: 'pointer'
  }),
  '& a:not(.MuiAvatar-root)': {
    color: theme.palette.text.primary,
    textDecoration: 'underline',
    cursor: 'pointer'
  },
  ...(isOwner && {
    color: theme.palette.text.secondary,
    marginRight: theme.spacing(1),
    marginLeft: 0
  })
}));

const UIMsgAttachmentAuthor = styled('div', {
  name,
  slot: 'Author'
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'flex-start',
  alignItems: 'center',
  '& .MuiAvatar-root': {
    fontSize: theme.mixins.pxToRem(7)
  },
  strong: {
    padding: theme.spacing(0, 0.5)
  }
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
  slot: 'Text'
})(({ theme }) => ({
  color: theme.palette.text.secondary,
  margin: theme.spacing(0.5, 0)
}));
const UIMsgAttachmentTextDelete = styled('div', {
  name,
  slot: 'TextDelete'
})(({ theme }) => ({
  margin: theme.spacing(0.5),
  marginLeft: 0,
  fontStyle: 'italic'
}));
const UIMsgAttachmentInfoWrapper = styled('div', {
  name,
  slot: 'AttachmentInfo'
})<{ msgType?: string; isOther?: boolean }>(({ theme }) => ({
  overflow: 'hidden',
  margin: theme.spacing(1, 1, 0, 0)
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

const AuthorNameStyled = styled(Typography)(({ theme }) => ({
  color:
    theme.palette.mode === 'light'
      ? theme.palette.grey['A200']
      : theme.palette.text.primary
}));

const UIMsgAttachmentLink = styled(TruncateText, {
  name,
  slot: 'uiMsgAttachmentLink'
})(({ theme }) => ({
  '& .ico': {
    fontSize: theme.mixins.pxToRem(12),
    marginRight: theme.spacing(0.5)
  }
}));

const LoadingStyled = styled('div', { name, slot: 'LoadingStyled' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    position: 'absolute',
    width: '100%',
    height: '100%',
    backgroundColor: alpha(theme.palette.background.paper, 0.7)
  })
);

interface MsgQuoteProps extends MsgItemShape {
  mentions?: any;
  isOwner?: boolean;
  user: UserItemShape;
  message: string;
  type: string;
  attachments?: any;
}

interface Props {
  dataQuote: MsgQuoteProps;
  isOwner?: boolean;
  isSearch?: boolean;
}

export default function MsgQuote({
  dataQuote: item,
  isOwner,
  isSearch = false
}: Props) {
  const { i18n, dispatch } = useGlobal();
  const messagesDelete = useChatRoomMsgDelete(item?.room_id) || [];
  const [loading, setLoading] = React.useState(false);
  const chatRoom = useChatRoom(item?.room_id);

  const { msgSearch } = chatRoom || {};

  const { multiImageFile, attachments } = useCheckImageQuote(item?.attachments);

  if (!item) return null;

  const { user, message } = item;

  const type = convertTypeMessage({ msg: item, messagesDelete });

  const handleSuccess = () => {
    setLoading(false);
  };

  const handleClick = () => {
    if (isSearch || type === 'messageDeleted' || loading) return;

    setLoading(true);

    dispatch({
      type: CHAT_JUMP_MSG_ACTION,
      payload: {
        roomId: item?.room_id,
        mid: item?.id,
        limit: 20,
        operate: 'all',
        mode: 'quote'
      },
      meta: { onSuccess: handleSuccess }
    });
  };

  return (
    <RootMsg isOwner={isOwner} onClick={handleClick} isSearch={isSearch}>
      {!isEmpty(msgSearch) && loading ? (
        <LoadingStyled data-testid="loadingIndicator">
          <CircularProgress size={20} />
        </LoadingStyled>
      ) : null}
      <DividerWrapperStyled>
        <DividerStyled />
      </DividerWrapperStyled>
      <UIMsgAttachmentInfoWrapper>
        {user ? (
          <UIMsgAttachmentAuthor>
            <AuthorNameStyled component="h2" variant="h5">
              {user.full_name || user.user_name}
            </AuthorNameStyled>
          </UIMsgAttachmentAuthor>
        ) : null}
        <UIMsgAttachmentFlex isAttachment={attachments.length}>
          {type === 'messageDeleted' ? (
            <UIMsgAttachmentTextDelete>
              {i18n.formatMessage({ id: 'message_was_deleted' })}
            </UIMsgAttachmentTextDelete>
          ) : null}
          {!isEmpty(message) && type !== 'messageDeleted' ? (
            <UIMsgAttachmentText
              lines={1}
              dangerouslySetInnerHTML={{
                __html: formatTextMsg(message)
              }}
            />
          ) : null}

          {type !== 'messageDeleted' && attachments && attachments?.length ? (
            <Box sx={{ mt: 1 }}>
              {multiImageFile.length > 1 ? (
                <UIMsgAttachmentText lines={1}>
                  {i18n.formatMessage(
                    { id: 'total_photo' },
                    { value: multiImageFile.length }
                  )}
                </UIMsgAttachmentText>
              ) : (
                <>
                  {attachments.map((item, i) => (
                    <React.Fragment key={`k${i}`}>
                      {item.is_image && item.image ? (
                        <MsgAttachment
                          item={item}
                          isOwner={isOwner}
                          allowOpenPreview={false}
                        />
                      ) : (
                        <UIMsgAttachmentLink lines={1}>
                          <LineIcon icon="ico-paperclip-alt" />
                          {item.file_name}
                        </UIMsgAttachmentLink>
                      )}
                    </React.Fragment>
                  ))}
                </>
              )}
            </Box>
          ) : null}
        </UIMsgAttachmentFlex>
      </UIMsgAttachmentInfoWrapper>
    </RootMsg>
  );
}
