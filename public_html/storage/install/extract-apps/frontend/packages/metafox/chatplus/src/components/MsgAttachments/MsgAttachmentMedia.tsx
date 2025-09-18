import { useGlobal } from '@metafox/framework';
import React from 'react';
import MsgAttachmentAudio from './MsgAttachmentAudio';
import MsgAttachmentImage from './MsgAttachmentImage';
import MsgAttachmentMultiImage from './MsgAttachmentMultiImage';
import MsgAttachmentVideo from './MsgAttachmentVideo';

export interface Props {
  image_url: string;
  image_dimensions?: { width: number; height: number };
  video_url?: string;
  title: string;
  video_type?: string;
  video_thumb_url?: string;
  audio_url?: string;
  layout?: string;
  totalImages?: string[];
  isOwner?: boolean;
  typeGridLayout?: string;
  msgType?: 'message_pinned' | 'message_unpinned' | string;
  isOther?: boolean;
  keyIndex?: any;
  allowOpenPreview?: boolean;
  audio_duration?: number;
  video_width?: number;
  video_height?: number;
}

export default function MsgAttachmentMedia({
  image_url,
  image_dimensions,
  video_url,
  title,
  video_type,
  video_thumb_url,
  audio_url,
  layout,
  totalImages,
  isOwner,
  msgType,
  isOther,
  keyIndex,
  allowOpenPreview = true,
  audio_duration,
  ...rest
}: Props) {
  const { chatplus } = useGlobal();

  if (audio_url) {
    return (
      <MsgAttachmentAudio
        audio_url={chatplus.sanitizeRemoteFileUrl(audio_url)}
        audio_duration={audio_duration}
        msgType={msgType}
        isOwner={isOwner}
        isOther={isOther}
      />
    );
  }

  if (video_url) {
    return (
      <MsgAttachmentVideo
        isOwner={isOwner}
        video_url={video_url}
        video_type={chatplus.sanitizeRemoteFileUrl(video_type)}
        thumb_url={chatplus.sanitizeRemoteFileUrl(video_thumb_url)}
        allowOpenPreview={allowOpenPreview}
        {...rest}
      />
    );
  }

  if (image_url && layout === 'multi-image') {
    return (
      <MsgAttachmentMultiImage
        title={title}
        images={totalImages}
        image_url={image_url}
        isOwner={isOwner}
        msgType={msgType}
        isOther={isOther}
        keyIndex={keyIndex}
      />
    );
  }

  if (image_url) {
    return (
      <MsgAttachmentImage
        isOwner={isOwner}
        title={title}
        image_dimensions={image_dimensions}
        image_url={image_url}
        allowOpenPreview={allowOpenPreview}
      />
    );
  }

  return null;
}
