/**
 * @type: service
 * name: popoverBackend
 */
import { Manager } from '@metafox/framework/Manager';
import * as React from 'react';
import { matchPath } from 'react-router';

type PopoperParams = any;

export type PopoverHandlerConfig = {
  component: string;
  path: string;
};

export type PopoverConfig = {
  presentDelay: number;
  dismissDelay: number;
};

type TSubscription = (data: PopoperParams) => void;

export default class PopoverBackend {
  /**
   * forcing to popup key
   */
  public static configKey: string = 'popoverHandlers';

  private handlers: PopoverHandlerConfig[];

  private config: PopoverConfig;

  manager: Manager;

  /**
   * HtmlElement
   */
  private anchorEl: HTMLElement;

  private presentTimer: any;
  private dismissTimer: any;

  private subscriber: TSubscription;

  constructor(handlers: PopoverHandlerConfig[]) {
    this.handlers = handlers ?? [];
    this.config = {
      presentDelay: 500,
      dismissDelay: 350
    };
  }

  public bootstrap(manager: Manager) {
    this.manager = manager;
  }

  public match(pathname: string) {
    for (let i = 0; i < this.handlers.length; ++i) {
      const handler = this.handlers[i];
      const result = matchPath(
        {
          path: handler.path
        },
        pathname
      );

      if (result) {
        return { component: handler.component, props: result.params };
      }
    }

    return null;
  }

  /**
   * load data description.
   * based on item view & other content.
   */
  public prefetch(path: string) {}

  private present = () => {
    if (!this.anchorEl) return null;

    const pathname = this.anchorEl.dataset.popover;

    if (!pathname) return;

    const content = this.match(pathname);

    if (!content) return;

    const Component = this.manager.jsxBackend.get(content.component);

    if (!Component) return;

    this.notifyChanged({
      anchorEl: this.anchorEl,
      open: true,
      content
    });
  };

  private dismiss = (): void => {
    this.notifyChanged({
      open: false
    });
  };

  public subscribe(fn: TSubscription): void {
    this.subscriber = fn;
  }

  public unsubscribe(): void {
    this.subscriber = null;
  }

  public onExited = (): void => {
    // remove hidden state
  };

  public onEnterContent = (evt: React.SyntheticEvent<HTMLElement>): void => {
    this.clearAllTimer();
  };

  public onLeaveContent = (evt: React.SyntheticEvent<HTMLElement>): void => {
    this.clearAllTimer();
    this.tickLeave(this.dismiss, this.config.dismissDelay);
  };

  public onEnterAnchor = (evt: React.SyntheticEvent<HTMLElement>): void => {
    const node = evt.currentTarget;

    if (this.anchorEl && this.anchorEl !== node) {
      this.dismiss();
    }

    this.anchorEl = node;

    this.clearAllTimer();

    this.tickEnter(this.present, this.config.presentDelay);
  };

  public onLeaveAnchor = (evt: React.SyntheticEvent<HTMLElement>): void => {
    this.clearAllTimer();
    this.tickLeave(this.dismiss, this.config.dismissDelay);
  };

  public notifyChanged(data: PopoperParams) {
    if (this.subscriber) {
      this.subscriber(data);
    }
  }

  public tickEnter(cb: () => void, delay: number) {
    this.clearPresentTimer();
    this.presentTimer = setTimeout(cb, delay);
  }

  public clearPresentTimer() {
    if (this.presentTimer) window.clearTimeout(this.presentTimer);

    this.presentTimer = 0;
  }

  private tickLeave(cb: () => void, delay: number): void {
    this.clearDismissTimer();
    this.dismissTimer = window.setTimeout(cb, delay);
  }

  private clearDismissTimer(): void {
    if (this.dismissTimer) window.clearTimeout(this.dismissTimer);

    this.dismissTimer = 0;
  }

  private clearAllTimer() {
    this.clearPresentTimer();
    this.clearDismissTimer();
  }
}
