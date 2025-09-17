export interface Step {
  title: string;
  dataSource: { apiUrl: string; apiMethod?: string };
  data?: object;
  message?: string;
}
export interface State {
  steps: Step[];
  current: number;
  done?: boolean;
}

export interface AppItemShape {
  identity: string;
  name: string;
  version: string;
  required: boolean;
}
