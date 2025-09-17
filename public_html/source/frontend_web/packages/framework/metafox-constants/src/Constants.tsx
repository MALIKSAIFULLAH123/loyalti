/**
 * @type: service
 * name: constants
 */
import { Manager } from '@metafox/framework/Manager';
import { randomId } from '@metafox/utils';

export default class Constants {
  public isLayoutPreviewWindow: boolean = false;
  public previewDevice: string = '';
  public readonly tunnelId: string;

  constructor() {
    this.tunnelId = randomId();
  }

  bootstrap(manager: Manager) {
    manager.deps('cookieBackend');
    const topWindow = window?.top as any;
    const selfWindow = window as any;
    const cookie = manager.cookieBackend;

    this.isLayoutPreviewWindow =
      topWindow !== selfWindow && 'layoutPreviewWindow' === selfWindow.name;
    this.previewDevice = cookie.get('previewDevice') || '';
  }
}
