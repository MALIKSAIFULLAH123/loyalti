/**
 * @type: service
 * name: dialogBackend
 */
import { isString, uniqueId } from 'lodash';
import DialogProvider from './DialogProvider';
import {
  AlertParams,
  ConfirmParams,
  DialogBackendConfig,
  ModalItemParams
} from './types';

class DialogBackend {
  /**
   * get config section in manager
   */
  public static readonly configKey: string = 'dialog';

  /**
   * list of data
   */
  private items: ModalItemParams[] = [];

  /**
   * private config
   */
  private readonly config: DialogBackendConfig;

  /**
   * local listeners
   */
  private readonly listeners: ((items: ModalItemParams[]) => void)[] = [];

  /**
   * @param config
   */
  constructor(config: Partial<DialogBackendConfig>) {
    this.config = Object.assign({}, config);
    this.present = this.present.bind(this);
    this.prompt = this.prompt.bind(this);
    this.dismiss = this.dismiss.bind(this);
    this.confirm = this.confirm.bind(this);
    this.alert = this.alert.bind(this);
    this.onClose = this.onClose.bind(this);
    this.onExited = this.onExited.bind(this);
    this.comingSoon = this.comingSoon.bind(this);
  }

  public bootstrap() {}

  public present<T = any, P = Record<string, any>>(
    content: ModalItemParams<P>
  ): Promise<T> {
    const dialogId = content.dialogId || uniqueId('dialog');
    const permanent = dialogId === content.dialogId;
    const testid =
      content.dialogId ?? isString(content.component)
        ? content.component
        : undefined;

    const exitedItem = permanent
      ? this.items.find(item => item.dialogId === dialogId)
      : undefined;

    const result = new Promise<T>(resolve => {
      if (exitedItem) {
        exitedItem.open = true;
        exitedItem.props.content = content;
        exitedItem.props.resolve = resolve; // fetch resolve
      } else {
        this.items.push({
          component: DialogProvider,
          open: true,
          dialogId,
          permanent, // mark is keep for permanent and does not remove until forceClose
          props: {
            key: dialogId,
            content,
            testid,
            resolve,
            onClose: () => this.onClose(dialogId),
            onExited: () => this.onExited(dialogId),
            forceClose: () => this.onClose(dialogId, true)
          }
        });
      }
    });
    this.notifyChanged();

    return result;
  }

  public subscribe(sub: (items: ModalItemParams[]) => void): string {
    const token = uniqueId();
    this.listeners[token] = sub;

    return token;
  }

  public unsubscribe = (token: string): void => {
    delete this.listeners[token];
  };

  private notifyChanged(): void {
    const lastIndex = this.items.length - 1;
    const x = this.items.map(
      ({ component, dialogId, open, props: ownProps }, index) => ({
        component,
        open,
        dialogId,
        props: { ...ownProps, open, isLast: index === lastIndex }
      })
    );

    Object.values(this.listeners).forEach(sub => sub(x));
  }

  private onClose(dialogId: string, permanent?: boolean) {
    const item = this.items.find(item => item.dialogId === dialogId);

    if (item) {
      item.open = false;

      if (permanent) {
        item.permanent = false;
      }
    }

    this.notifyChanged();
  }

  private onExited(dialogId: string) {
    this.items = this.items.filter(
      item => item.permanent || item.dialogId !== dialogId
    );
    this.notifyChanged();
  }

  /**
   * Remove dialog from present items
   *
   * @param  {Boolean} all
   * @returns
   */
  public dismiss(all?: boolean): void {
    if (!this.items.length) {
      return;
    }

    if (all) this.items = this.items.filter(item => item.open !== false);

    if (all) {
      this.items = this.items.filter(x => x.open !== false);
    }

    if (all) {
      this.items.forEach(x => (x.open = false));
    } else {
      const last = this.items.pop();
      last.open = false;
      this.items.push(last);
    }

    this.notifyChanged();
  }
  public dismissById(dialogId: string, permanent?: boolean): void {
    if (!this.items.length) {
      return;
    }

    const item = this.items.find(item => item.dialogId === dialogId);

    if (item) {
      item.open = false;

      if (permanent) {
        item.permanent = false;
      }
    }

    this.notifyChanged();
  }

  /**
   * Show confirm
   * @param props
   * @returns
   */
  public confirm(props: ConfirmParams): Promise<boolean> {
    return this.present<boolean>({
      component: 'ui.dialog.confirm',
      props
    }).then(choose => {
      return !!choose;
    });
  }
  public alert(props: AlertParams): Promise<void> {
    return this.present<void>({
      component: 'ui.dialog.alert',
      props
    }).catch(void 0);
  }

  public prompt<T = string>(
    props: AlertParams & {
      label: string;
      helperText?: string;
    }
  ) {
    return this.present<T>({
      component: 'ui.dialog.prompt',
      props
    }).catch(void 0);
  }

  public comingSoon() {
    this.alert({
      title: 'Alert',
      message: 'Oops, this feature is coming soon!'
    });
  }
}

export default DialogBackend;
