/**
 * @type: service
 * name: eventCenter
 */

import { randomId } from '@metafox/utils';

type Listener = (data: any) => void;

type Events = Record<string, { token: string; fn: Listener }[]>;

export default class EventCenter {
  private readonly events: Events = {};

  private previewEventCenter: EventCenter;

  public bootstrap() {
    const topWindow = window?.top as any;
    const selfWindow = window as any;

    try {
      if (topWindow?.eventCenter) {
        topWindow.eventCenter.previewEventCenter = this;
      }

      selfWindow.eventCenter = this;
    } catch (error) {}
  }

  public on(name: string, listener: Listener): string {
    if (!this.events[name]) this.events[name] = [];

    const token = randomId();

    this.events[name].push({ token, fn: listener });

    return token;
  }

  public off(name: string, token: string): void {
    if (!this.events[name]) return;

    this.events[name] = this.events[name].filter(item => item.token !== token);
  }

  public dispatch(name: string, data?: any) {
    if (!this.events[name]) return;

    if (this.previewEventCenter) {
      this.previewEventCenter.dispatch(name, data);
    }

    this.events[name]
      .map(x => x.fn)
      .filter(x => x)
      .forEach(x => x(data));
  }

  public removePreviewCenter() {
    this.previewEventCenter = undefined;
  }
}
