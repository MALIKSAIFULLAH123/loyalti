import { printlog } from '@metafox/utils';
import type {
  AuthResultShape,
  DdpCallback,
  DdpClientContext,
  DdpMethodParams,
  DdpSubListener,
  DdpSubParams,
  DdpSubscribe
} from '../types';
import { Manager } from '@metafox/framework';

let startId = 1;
const nextId = () => `c:${++startId}`;

/**
 * Implement DdpClient service
 */
export default class DdpClient {
  /**
   * Debug mode
   */
  private debug: boolean;

  /**
   * Manager
   */
  private manager: Manager;

  /**
   * @private
   */
  private websocket: WebSocket;

  /**
   * @private
   */
  private loginRetry: boolean = false;

  /**
   * @private
   */
  private _isLoggedIn: boolean = false;

  /**
   * @private
   */
  private readonly _updatedCallbacks: { [id: string]: DdpCallback } = {};

  /**
   * @private
   */
  private readonly _callbacks: { [id: string]: DdpCallback } = {};

  /**
   * @private
   */
  private readonly _pendingMethods: { [id: string]: true } = {};

  /**
   * @private
   */
  private readonly _subscriptions: { [id: string]: DdpSubscribe } = {};

  /**
   * @private
   */
  private readonly _eventNames: { [id: string]: DdpSubListener } = {};

  private context: DdpClientContext;

  private onLoggedIn: DdpCallback = () => null;

  public on(listeners: DdpSubListener[]): string[] {
    return listeners.map(method => {
      const id = nextId();
      this._eventNames[id] = method;

      return id;
    });
  }

  public off(ids: string[]) {
    ids.forEach(id => {
      delete this._eventNames[id];
    });
  }

  constructor(debug: boolean = false, manager: Manager) {
    this.debug = debug;
    this.manager = manager;
  }

  /**
   * is ready
   */
  public isLoggedIn(): boolean {
    return this._isLoggedIn;
  }

  public subscribe({ name, id, params, callback }: DdpSubParams): string {
    id = id || nextId();

    if (callback) {
      this._subscriptions[id] = callback;
    }

    this._send({
      msg: 'sub',
      name,
      id,
      params
    });

    return id;
  }

  public unsubscribe(id: string): void {
    if (id && this._subscriptions[id]) {
      delete this._subscriptions[id];
      this._send({
        msg: 'unsub',
        id
      });
    }
  }

  public useMethod({
    name,
    params = [],
    id,
    updatedCallback,
    callback
  }: DdpMethodParams): void {
    id = id || nextId();
    this._callbacks[id] = (error, result) => {
      delete this._pendingMethods[id];

      if (callback) {
        callback(error, result);
      }
    };
    this._updatedCallbacks[id] = (error, result) => {
      delete this._pendingMethods[id];

      if (updatedCallback) {
        updatedCallback(error, result);
      }
    };
    this._pendingMethods[id] = true;

    this._send({
      msg: 'method',
      method: name,
      id,
      params
    });
  }

  public connect(context: DdpClientContext, onLoggedIn: DdpCallback): void {
    this.context = context;
    this.onLoggedIn = onLoggedIn;

    if (!context?.socketUrl) return;

    this.websocket = new WebSocket(context.socketUrl);

    this.websocket.onopen = () => {
      if (this.websocket.readyState === WebSocket.OPEN) {
        this._send({
          msg: 'connect',
          version: '1',
          support: ['1', 'pre2', 'pre1']
        });
      }
    };

    this.websocket.onclose = (e: CloseEvent) => {
      try {
        if (this.manager) {
          this.manager.dispatch({
            type: 'chatplus/reInit'
          });
        }
      } catch (error) {}

      // normal closed
      // setTimeout(() => this.connect(context, onLoggedIn), 5000);
    };

    this.websocket.onerror = error => {
      if (this.debug) printlog({ error });
    };

    this.websocket.onmessage = evt => this.onMessageEvent(evt);
  }

  public loginWithResume(resume: string): Promise<AuthResultShape> {
    return new Promise((resolve, reject) => {
      this.useMethod({
        name: 'login',
        id: 'result/login',
        params: [{ resume }],
        callback: (error, result) => {
          if (error) {
            reject(error);
          } else {
            resolve(result);
          }
        }
      });
    });
  }

  public loginWithAccessToken(accessToken: string): Promise<AuthResultShape> {
    return new Promise((resolve, reject) => {
      this.useMethod({
        name: '__oauthLogin',
        id: 'result/__oauthLogin',
        params: [accessToken],
        callback: (error, result) => {
          if (error) {
            reject(error);
          } else {
            resolve(result);
          }
        }
      });
    });
  }

  public login(
    resume: string,
    useCached: boolean = true
  ): Promise<AuthResultShape> {
    const { loadResumeToken, saveResumeToken, loadAccessToken } = this.context;

    if (!resume && useCached) {
      resume = loadResumeToken();
    } else {
      saveResumeToken(resume);
    }

    if (resume) {
      this.context.token = resume;
    }

    if (resume) {
      return this.loginWithResume(resume);
    } else {
      return loadAccessToken().then(accessToken =>
        this.loginWithAccessToken(accessToken)
      );
    }
  }

  private onMsgNoSub({ id, error }): void {
    const cb = this._callbacks[id];

    if (cb) {
      cb(error);
      delete this._callbacks[id];
    }
  }

  public onMessageEvent(evt?: MessageEvent): void {
    try {
      if (!evt || !evt.data) return;

      const data = JSON.parse(evt.data); // to json.

      if (data.error) {
        // eslint-disable-next-line
        if (this.debug) printlog(data.error);
      }

      switch (data.msg) {
        case 'failed':
          this.onMsgFailed();
          break;
        case 'ping':
          this.onMsgPing(data);
          break;
        case 'connected':
          this.login(null, true);
          break;
        case 'updated': {
          this.onMsgUpdated(data);
          break;
        }
        case 'nosub':
          this.onMsgNoSub(data);
          break;
        case 'changed':
          this.onMsgChanged(data);
          break;
        case 'result':
          this.onMsgResult(data);
          break;
      }
    } catch (error) {
      if (this.debug) printlog(error);
    }
  }

  /**
   * @param methods
   * @private
   */
  private onMsgUpdated({ methods }): void {
    if (methods && Array.isArray(methods)) {
      methods.forEach(method => {
        const cb = this._updatedCallbacks[method];

        if (cb) {
          cb();
          delete this._updatedCallbacks[method];
        }
      });
    }
  }

  private onMsgFailed(): void {
    // do nothing
  }

  private onMsgPing({ id }): void {
    this._send(id ? { msg: 'pong', id } : { msg: 'pong' });
  }

  private onMsgChanged({ collection, fields: { args, eventName } }): void {
    if (eventName) {
      this._trigger(collection, eventName, args);
    }
  }

  /**
   * @param data
   * @private
   */
  private _send(data: any) {
    if (this.debug) {
      // printlog(data);
    }

    if (this.websocket && this.websocket.readyState === WebSocket.OPEN) {
      this.websocket.send(JSON.stringify(data));
    }
  }

  private _trigger(collection: string, eventName: string, args: any): void {
    if (eventName) {
      Object.values(this._eventNames)
        .filter(event => event.match(eventName, collection))
        .forEach(({ callback }) => {
          callback(eventName, args);
        });
    }
  }

  private onMsgResult({ error, id, result }: any): void {
    const cb = this._callbacks[id];

    if (cb) {
      cb(error, result);
      delete this._callbacks[id];
    }

    if ('result/login' === id) {
      if (error) {
        if (!this.loginRetry) {
          this.login(null, false);
        } else {
          this.loginRetry = true;
          this.login(null, false);
        }
      } else {
        this._isLoggedIn = true;
        this.onLoggedIn(error, result);
      }
    } else if ('result/__oauthLogin' === id) {
      if (error) {
        this.loginRetry = true;
        this.onLoggedIn(error, result);
      } else {
        this.login(result.authToken);
      }
    }
  }
}
