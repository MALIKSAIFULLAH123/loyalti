import { ButtonProps, DialogProps } from '@mui/material';

export interface ModalItemParams<P = Record<string, any>> {
  component: string | React.ElementType;
  dialogId?: string;
  permanent?: boolean;
  open?: boolean;
  props?: P;
}

export interface TDialogProvider {
  testid?: string;
  content: { component: string; props: Record<string, any> };
  type: string;
  resolve(value: any): void;
  isLast: boolean;
  onClose?: () => void;
  forceClose?: () => void;
  open?: boolean;
  onEnter?: () => void;
  onEntering?: () => void;
  onEntered?: () => void;
  onExit?: () => void;
  onExiting?: () => void;
  onExited?: () => void;
  backdropClose?: boolean;
}

interface AlertButtonProps {
  label: string;
  color?: ButtonProps['color'];
  variant?: ButtonProps['variant'];
  size?: ButtonProps['size'];
}

export interface DialogBackendConfig {}

export interface AlertParams {
  title?: React.ReactNode;
  message?: React.ReactNode;
  positiveButton?: AlertButtonProps;
  maxWidth?: DialogProps['maxWidth'];
  disableClose?: boolean;
  showDialogActions?: boolean;
}

export interface TModalDialogProps {
  open: boolean;
  onClose(): void;
  onExited(): void;
  onEnter?(): void;
  onEntered?(): void;
  onEntering?(): void;
  onEscapeKeyDown?(): void;
  onExit?(): void;
  onExiting?(): void;
}
export interface AlertDialogProps extends AlertParams, TModalDialogProps {}

export interface ConfirmParams {
  title?: React.ReactNode;
  message: string | React.ReactNode;
  positiveButton?: AlertButtonProps;
  negativeButton?: AlertButtonProps;
  phraseParams?: Record<string, any>;
}
export type UserConfirmCallback =
  | (() => ConfirmParams | boolean | undefined)
  | ConfirmParams
  | boolean
  | undefined;

export interface DialogItemContext {
  type: string;
  dialogProps?: DialogProps;
  closeDialog: () => void;
  forceClose: () => void;
  setDialogValue: (value: unknown) => void;
  setDialogResolveValue: (value: unknown) => void;
  setUserConfirm: (
    callback?: UserConfirmCallback,
    shouldCheckNavigation?: boolean
  ) => void;
  disableBackdropClick: (disabled: boolean) => void;
}
