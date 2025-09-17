/**
 * @type: service
 * name: apiClient
 */
import { Manager } from '@metafox/framework/Manager';
import { REFRESH_TOKEN } from '@metafox/framework';
import { MFOX_API_URL } from '@metafox/framework/constants';
import axios, { AxiosInstance } from 'axios';
import interpolateUrl from './interpolateUrl';
import { RestClientConfig } from './types';
import { get } from 'lodash';

export default class RestClient {
  public static readonly configKey: string = 'root.api';

  readonly config: RestClientConfig;

  constructor(config: Partial<RestClientConfig>) {
    this.config = Object.assign({}, config);
  }
  /**
   * @param config
   */
  public bootstrap(manager: Manager): AxiosInstance {
    manager.deps('cookieBackend');

    const instance = axios.create({
      baseURL: MFOX_API_URL
    });

    // require computed on run time
    const computeToken = () => {
      const token = manager.cookieBackend.get('token');

      if (token) return `Bearer ${token ?? ''}`;

      return undefined;
    };

    const getRefreshToken = () => {
      return manager.cookieBackend.get('refreshToken');
    };

    instance.interceptors.request.use(
      config => {
        const rq = interpolateUrl(config.url, config.params);
        config.url = rq.pathname;
        config.params = rq.searchParams;
        config.headers['X-Date'] = manager?.moment().toString();

        const token = computeToken();

        if (token && undefined === config.headers.Authorization) {
          config.headers.Authorization = token;
        }

        return config;
      },
      error => Promise.reject(error)
    );

    // TODO: take care about this method.
    instance.interceptors.response.use(
      response => {
        return response;
      },
      error => {
        if (error?.response) {
          // check server error
          const statusCode = get(error, 'response.status');
          const { refresh_retry } = get(error, 'response.headers', {});

          if (statusCode === 401 && refresh_retry && manager.dispatch) {
            const refreshToken = getRefreshToken();

            if (refreshToken) {
              manager.dispatch({
                type: REFRESH_TOKEN
              });
            }
          }

          if (statusCode === 426 && manager.dispatch) {
            manager.dispatch({
              type: '@redirectTo',
              payload: error.response.data
            });

            return;
          }
        }

        throw error;
      }
    );

    return instance;
  }
}
