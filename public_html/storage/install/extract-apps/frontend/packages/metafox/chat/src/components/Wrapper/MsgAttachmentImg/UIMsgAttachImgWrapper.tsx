import { styled } from '@mui/material';
import React from 'react';
import { MsgAttachmentImgProps } from '../type';

const name = 'UIMsgAttachmentImgWrapper';

const RootStyled = styled('div', { name, slot: 'RootStyled' })(({ theme }) => ({
  position: 'relative',
  width: '100%'
}));

const MediaWrapper = styled('figure', { name, slot: 'MediaWrapper' })(
  ({ theme }) => ({
    margin: 0,
    display: 'block'
  })
);

const ImgRatioWrapper = styled('div', {
  name,
  slot: 'ImgRatioWrapper',
  shouldForwardProp: props =>
    props !== 'isOwner' && props !== 'isPageAllMessages'
})<{ isOwner?: boolean; isPageAllMessages?: boolean }>(
  ({ theme, isOwner, isPageAllMessages }) => ({
    width: '100%',
    minWidth: isOwner ? '200px' : '180px',
    maxWidth: '100%',
    cursor: 'pointer',
    ...(isPageAllMessages && {
      width: '300px',
      [theme.breakpoints.down('sm')]: {
        width: isOwner ? '200px' : '180px'
      }
    })
  })
);

export default function UIMsgAttachmentImgWapper({
  children,
  ratioImage,
  isPageAllMessages,
  isOwner
}: MsgAttachmentImgProps) {
  return (
    <RootStyled>
      <MediaWrapper>
        <ImgRatioWrapper
          isOwner={!!isOwner}
          isPageAllMessages={isPageAllMessages}
          style={{ paddingBottom: `${ratioImage ? ratioImage * 100 : 100}%` }}
        >
          {children}
        </ImgRatioWrapper>
      </MediaWrapper>
    </RootStyled>
  );
}
