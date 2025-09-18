import { triggerClick } from '@metafox/chat/utils';
import { styled } from '@mui/material';
import React from 'react';
import MsgAttachmentImage from './MsgAttachmentImage';
import MsgAttachmentMultiImage from './MsgAttachmentMultiImage';

const name = 'MsgAttachmentMedia';

const StyledFileAttachment = styled('div', {
  name,
  slot: 'TitleLink',
  shouldForwardProp: prop => prop !== 'isOwner'
})<{
  isOwner?: boolean;
}>(({ theme, isOwner }) => ({
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
  })
}));

const IconWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  cursor: 'pointer',
  '& .ico': {
    marginRight: theme.spacing(1)
  }
}));
export interface Props {
  isOwner?: boolean;
  layout?: 'multi-image' | string;
  totalImages?: string[];
  is_image?: boolean;
  image?: any;
  file_name: any;
  download_url: any;
  file_size_text?: any;
  image_url?: string;
  image_dimensions?: { width: number; height: number };
  video_url?: string;
  title?: string;
  video_type?: string;
  video_thumb_url?: string;
  audio_url?: string;
  isOther?: boolean;
  keyIndex?: string;
  id: string;
  allowOpenPreview?: boolean;
}

export default function MsgAttachmentMedia({
  isOwner,
  layout,
  totalImages,
  is_image,
  image,
  file_name,
  download_url,
  file_size_text,
  keyIndex,
  id,
  allowOpenPreview = true
}: Props) {
  if (image && layout === 'multi-image') {
    return (
      <MsgAttachmentMultiImage
        images={totalImages}
        file_name={file_name}
        image={image}
        download_url={download_url}
        isOwner={isOwner}
        keyIndex={keyIndex}
        id={id}
        allowOpenPreview={allowOpenPreview}
      />
    );
  }

  if (is_image && image) {
    return (
      <MsgAttachmentImage
        isOwner={isOwner}
        image={image}
        file_name={file_name}
        download_url={download_url}
        id={id}
        allowOpenPreview={allowOpenPreview}
      />
    );
  }

  if (!is_image) {
    return (
      <StyledFileAttachment isOwner={isOwner}>
        <IconWrapper onClick={() => triggerClick(download_url)}>
          <i className={'ico ico-arrow-down-circle mr-1'} />
          {file_name}
        </IconWrapper>
      </StyledFileAttachment>
    );
  }

  return null;
}
