import { styled } from '@mui/material';
import React from 'react';
import { MsgAttachmentImgProps } from '../type';

const name = 'UIMsgAttachMultiImgWrapper';

const RootStyled = styled('div', {
  name,
  slot: 'RootStyled'
})(({ theme }) => ({
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
  shouldForwardProp: props => props !== 'isPageAllMessages'
})<{
  isPageAllMessages?: boolean;
}>(({ theme, isPageAllMessages }) => ({
  maxWidth: '100%',
  cursor: 'pointer',
  marginBottom: '1px',
  width: '100%',
  height: '100%',
  // 2. style page all
  ...(isPageAllMessages && {
    width: '100%',
    height: '100%'
  })
}));

export default function UIMsgAttachMultiImgWrapper({
  children,
  isPageAllMessages,
  ratioImage = 1
}: MsgAttachmentImgProps) {
  return (
    <RootStyled>
      <MediaWrapper>
        <ImgRatioWrapper
          isPageAllMessages={isPageAllMessages}
          style={{ paddingBottom: `${ratioImage ? ratioImage * 100 : 100}%` }}
        >
          {children}
        </ImgRatioWrapper>
      </MediaWrapper>
    </RootStyled>
  );
}
