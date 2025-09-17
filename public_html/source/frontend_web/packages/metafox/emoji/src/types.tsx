import { ButtonProps, PopperProps } from '@mui/material';

export type EmojiItemShape = string;

export type OnEmojiClick = (unicode: EmojiItemShape, shortcut?: string) => void;

export interface AttachEmojiButtonProps {
  onEmojiClick?: OnEmojiClick;
  multiple?: boolean;
  size?: ButtonProps['size'];
  buttonStyle?: React.CSSProperties;
  control?: React.FC<any>;
  disabled?: boolean;
  label?: string;
  scrollRef?: React.RefObject<HTMLDivElement>;
  scrollClose?: boolean;
  placement?: PopperProps['placement'];
  disablePortal?: boolean;
}

export interface EmojiSetShape {
  label: string;
  emojis: EmojiItemShape[];
}

export interface AppState {}
