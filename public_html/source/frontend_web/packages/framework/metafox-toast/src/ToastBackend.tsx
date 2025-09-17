/**
 * @type: service
 * name: toastBackend
 */
import { ToastBackendConfig, ToastBroker, ToastItemShape } from './types';

export default class ToastBackend {
  config: ToastBackendConfig;

  public static readonly configKey: string = 'toast';

  private broker: ToastBroker;

  constructor(config: Partial<ToastBackendConfig>) {
    this.config = Object.assign(
      {
        position: 'left|bottom',
        error: { duration: 10e3 },
        success: { duration: 5e3 },
        info: { duration: 5e3 },
        warning: { duration: 5e3 }
      },
      config
    );
  }

  public setBroker(broker: ToastBroker) {
    this.broker = broker;
  }

  public bootstrap() {}

  public show(toast: ToastItemShape) {
    if (this.broker) {
      this.broker(toast);
    }
  }

  public info(message: string, duration?: string): void {
    this.show({
      ...this.config.info,
      message,
      severity: 'info',
      duration: duration ?? this.config.info.duration
    });
  }

  public warning(message: string, duration?: string): void {
    this.show({
      ...this.config.warning,
      message,
      severity: 'warning',
      duration: duration ?? this.config.warning.duration
    });
  }

  public error(message: string, duration?: string): void {
    this.show({
      ...this.config.error,
      message,
      severity: 'error',
      duration: duration ?? this.config.error.duration
    });
  }

  public success(message: string, duration?: string): void {
    this.show({
      ...this.config.success,
      message,
      severity: 'success',
      duration: duration ?? this.config.success.duration
    });
  }
}
