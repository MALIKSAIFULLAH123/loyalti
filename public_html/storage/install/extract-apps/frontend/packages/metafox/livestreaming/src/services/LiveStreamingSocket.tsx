/**
 * @type: service
 * name: livestreamingSocket
 */
import { Manager } from '@metafox/framework';
import { UserItemShape } from '@metafox/user';
import { requireParam } from '@metafox/utils';
import {
  AuthResultShape,
  LivestreamConfig,
  DdpPromiseParams,
  DdpSubParams,
  IMeteorError,
  InitResultShape,
  SessionUserShape
} from '../types';
import DdpClient from './WsClient';

type TConnectStatus =
  | 'none'
  | 'connecting'
  | 'connected'
  | 'connect-failed'
  | 'initializing'
  | 'initialize-failed'
  | 'initialized';

type StreamingType = {
  stream_key: string;
};

type RawType = Blob;

export default class LivestreamingSocket {
  /**
   * Manager integration.
   */
  public static readonly configKey: string = 'root.livestreaming';

  /**
   * connect status
   */
  private status: TConnectStatus = 'none';

  /**
   * Handle configuration
   */
  private config: LivestreamConfig;

  /**
   * See manager pattern
   */
  private manager: Manager;

  /**
   * handle ddpClient
   */
  private ddpClient: DdpClient;

  private authResult: AuthResultShape;

  private authError: IMeteorError;

  private login: SessionUserShape;

  constructor() {
    this.waitDdpMethod = this.waitDdpMethod.bind(this);
  }

  public getConfig(): LivestreamConfig {
    return this.config;
  }

  public getLogin(): SessionUserShape {
    return this.login;
  }

  public bootstrap(manager: Manager) {
    this.manager = manager;
  }

  public isLoggedIn(): boolean {
    return !!this.authResult?.id;
  }

  public connect(): Promise<AuthResultShape> {
    const { socketUrl, ddpDebug } = this.config;

    if ('none' === this.status || 'connect-failed' === this.status) {
      requireParam(this.config, 'socketUrl');
      this.ddpClient = new DdpClient(ddpDebug, this.manager);
      const callback = (error: IMeteorError, user: AuthResultShape) => {
        if (error) {
          this.status = 'connect-failed';
          this.authError = error;
        } else {
          this.status = 'connected';
          this.authResult = user;
        }
      };

      this.status = 'connecting';

      this.ddpClient.connect(
        {
          socketUrl,
          loadAccessToken: this.loadAccessToken.bind(this),
          loadResumeToken: this.loadResumeToken.bind(this),
          saveResumeToken: this.saveResumeToken.bind(this)
        },
        callback
      );
    }

    return this.waitUntilLogin(10000);
  }
  // check to lock for re-connect.
  public init(
    user: UserItemShape,
    config: LivestreamConfig
  ): Promise<InitResultShape> {
    if (!config.authKey) {
      config.authKey = 'livestreamAuth';
    }

    this.config = config;

    if (['initialized', 'initializing'].includes(this.status)) {
      return;
    }

    return this.connect()
      .then(() => {
        this.status = 'initializing';

        return this.waitDdpMethod({
          name: '__init',
          params: []
        });
      })
      .then(data => {
        this.status = 'initialized';
        this.transformInitResult(data);

        return data;
      });
  }

  private transformInitResult(data: InitResultShape): void {
    this.login = data.login;
  }

  public getStatus() {
    return this.status;
  }

  private waitUntilLogin(
    timeout: number,
    interval: number = 500
  ): Promise<AuthResultShape> {
    let intervalId: any;
    let retry: number = timeout / interval;

    return new Promise((resolve, reject) => {
      intervalId = setInterval(() => {
        retry = retry - 1;

        if (this.authError) {
          clearInterval(intervalId);
          reject(this.authError);
        } else if (this.authResult) {
          clearInterval(intervalId);
          resolve(this.authResult);
        } else if (0 > retry) {
          reject('Connection Timeout');
        }
      }, interval);
    });
  }

  /**
   * load access token from current site?
   */
  public loadAccessToken(): Promise<string> {
    const { accessToken } = this.config;

    return new Promise((resolve, reject) => {
      if (accessToken) {
        resolve(accessToken);
      } else {
        reject('401');
      }
    });
  }

  /**
   * load resume token from site
   */
  public loadResumeToken(): string {
    const { authKey, resumeToken, userId } = this.config;

    if (resumeToken) {
      return resumeToken;
    }

    const cached = this.manager?.cookieBackend.get(authKey);

    if (cached && /:/.test(cached)) {
      const $a = cached.split(':');

      if ($a[0] === userId.toString()) {
        return $a[1];
      }
    }
  }

  public saveResumeToken(token: string): void {
    const { userId, authKey } = this.config;
    this.manager?.cookieBackend.set(authKey, `${userId}:${token}`);
  }

  public subscribe(items: DdpSubParams[]): string[] {
    return items.map(item => this.ddpClient.subscribe(item));
  }

  public unsubscribe(id: string[]): void {
    id.map(id => this.ddpClient.unsubscribe(id));
  }

  public waitDdpMethod<T = any>({
    name,
    id,
    params
  }: DdpPromiseParams): Promise<T> {
    const ddpClient = this.ddpClient;

    return new Promise<T>((resolve, reject) => {
      try {
        ddpClient.useMethod({
          name,
          id,
          params,
          callback: (err, result) => {
            if (err) {
              reject(err);
            } else {
              resolve(result);
            }
          }
        });
      } catch (err) {
        reject(err);
      }
    });
  }

  public send(data: Record<string, any>): void {
    const ddpClient = this.ddpClient;

    if (ddpClient) {
      ddpClient.send(data);
    }
  }

  public streaming(data: StreamingType): void {
    this.send({ ...data, msg: 'streaming' });
  }

  public sendRaw(data: RawType): void {
    const ddpClient = this.ddpClient;

    if (ddpClient) {
      ddpClient.sendRaw(data);
    }
  }

  public close(): void {
    const ddpClient = this.ddpClient;

    if (ddpClient) {
      this.status = 'none';
      this.authResult = null;
      this.authError = null;
      ddpClient.close();
    }
  }
}
