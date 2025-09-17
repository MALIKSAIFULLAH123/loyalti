import '@metafox/framework/Manager';
import { FetchDataConfig, RestClientConfig, TRestClient } from './types';
declare module '@metafox/framework/Manager' {
  interface Manager {
    apiClient?: TRestClient;
    useFetchDetail?: <T = any>(
      config: FetchDataConfig<T>
    ) => [T, boolean, string | undefined, unknown, unknown, unknown];
    useFetchItems?: <T = any>(
      config: FetchDataConfig<T>
    ) => [T, boolean, boolean | undefined, unknown];
  }

  interface ManagerConfig {
    api?: Partial<RestClientConfig>;
  }
}
