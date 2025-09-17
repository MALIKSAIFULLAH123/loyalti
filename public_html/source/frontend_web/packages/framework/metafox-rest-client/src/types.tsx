import { RemoteDataSource } from '@metafox/framework';
import { AxiosInstance } from 'axios';

export interface TRestClient extends AxiosInstance {}

export interface RestClientConfig {
  baseUrl?: string;
}

// data, loading, error, meta, message
export type FetchDataResult<T> = [T, boolean, string?, unknown?, unknown?];

export type FetchDataConfig<T = any> = {
  readonly dataSource: RemoteDataSource;
  readonly pageParams?: Record<string, any>;
  readonly data?: T;
  readonly cache?: boolean;
  readonly normalize?: boolean;
  readonly allowRiskParams?: boolean;
  readonly forceReload?: boolean;
  readonly preventReload?: boolean;
  readonly ttl?: number; // Cache time to life in milliseconds
  readonly cachePrefix?: string; // cache prefix to use etc: dataGrid.
  readonly cacheKey?: string;
  readonly key?: string;
};

export type TFetchDataFn = <T = any>(
  config: FetchDataConfig<T>
) => FetchDataResult<T>;
