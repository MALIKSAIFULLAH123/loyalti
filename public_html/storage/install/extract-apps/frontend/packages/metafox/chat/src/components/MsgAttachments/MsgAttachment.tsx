import { styled } from '@mui/material';
import MsgAttachmentMedia from './MsgAttachmentMedia';
import React from 'react';

const name = 'MsgAttachment';

const UIMsgAttachment = styled('div', {
  name,
  slot: 'UIMsgAttachment',
  shouldForwardProp: prop =>
    prop !== 'isOwner' &&
    prop !== 'isAudio' &&
    prop !== 'isTypeFile' &&
    prop !== 'isOther'
})<{
  isOwner?: boolean;
  isAudio?: boolean;
  isTypeFile?: boolean;
  isOther?: boolean;
}>(({ theme }) => ({
  maxWidth: '100%',
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-start',
  paddingBottom: theme.spacing(0.5),
  marginTop: theme.spacing(0.25),
  overflow: 'hidden'
}));

interface AttachmentItemShape {
  is_image?: boolean;
  image?: any;
  file_name: any;
  download_url: any;
  file_size_text?: any;
}

interface MsgAttachmentProps {
  isOwner?: boolean;
  item: AttachmentItemShape;
  allowOpenPreview?: boolean;
}

export default function MsgAttachment({
  item,
  isOwner,
  allowOpenPreview = true
}: MsgAttachmentProps) {
  const { is_image, image, file_name, download_url, file_size_text } = item;

  return (
    <UIMsgAttachment isOwner={isOwner}>
      <MsgAttachmentMedia
        isOwner={isOwner}
        image={image}
        is_image={is_image}
        download_url={download_url}
        file_name={file_name}
        file_size_text={file_size_text}
        {...item}
        allowOpenPreview={allowOpenPreview}
      />
    </UIMsgAttachment>
  );
}
