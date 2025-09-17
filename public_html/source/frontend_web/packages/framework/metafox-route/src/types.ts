export type Result =
  | false
  | {
      name: string;
      from?: string;
      to?: string;
      component: React.FC;
      params: object;
      pathname: string;
    };

export interface RouteBackendConfig {
  cache: boolean;
  pageNotFound: string;
  apiUrl: string;
}
