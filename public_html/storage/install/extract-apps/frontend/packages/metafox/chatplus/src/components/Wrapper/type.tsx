export interface MsgAttachmentImgProps {
  children: React.ReactNode;
  ratioImage?: any;
  isPageAllMessages?: boolean;
  isOwner?: boolean;
}

export interface MsgItemWrapperProps {
  children: React.ReactNode;
  isOwner?: boolean;
  isAlert?: boolean;
  isShowReact?: boolean;
}

export interface MsgItemBodyOuterProps {
  children: React.ReactNode;
  isMsgEdit?: boolean;
  totalImage?: any;
  isPageAllMessages?: boolean;
  msgType?: string;
  msgContentType?: string;
  totalImageQuote?: any;
}

export interface ComposerFormProps {
  children: React.ReactNode;
  className?: string;
  margin?: 'dense' | 'normal' | string;
}

export interface ComposerWrapperProps {
  children: React.ReactNode;
  onClick?: () => void;
}

export interface AttachIconsWrapperProps {
  children: React.ReactNode;
}
