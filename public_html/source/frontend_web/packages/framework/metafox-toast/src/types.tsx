import { AlertProps } from '@mui/lab';

export type ToastPosition =
  | 'left|top'
  | 'left|bottom'
  | 'right|top'
  | 'right|bottom'
  | 'bottom'
  | 'top';

export type ToastSeverityConfig = Partial<ToastItemShape>;

export interface ToastBackendConfig {
  position: ToastPosition;

  error: ToastSeverityConfig;

  success: ToastSeverityConfig;

  info: ToastSeverityConfig;

  warning: ToastSeverityConfig;
}

export interface ToastItemShape extends AlertProps {
  message: string;
  title?: string;
  duration?: number | 'manual';
  id?: string;
}

export type ToastBroker = ((toast: ToastItemShape) => void) | undefined;
