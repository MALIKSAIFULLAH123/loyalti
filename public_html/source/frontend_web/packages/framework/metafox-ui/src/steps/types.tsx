import { RenderItemBase } from '@metafox/framework/types';

export interface StepShape extends RenderItemBase {
  title: string;
  id: string;
  expanded?: boolean;
  completed?: boolean;
  disabled?: boolean;
}

export interface State {
  activeStep: number;
  activeId?: string;
  data: { steps: StepShape[] };
}

export interface StepsProps {
  steps: StepShape[];
}

export interface ProcessStepShape {
  title: string;
  enableReport?: boolean;
  disableRetry?: boolean;
  dryRun?: boolean;
  disableUserAbort?: boolean;
  timeout?: number;
  dataSource: { apiUrl: string; apiMethod?: string };
  data?: object; // passed to api request for POST, PUT
  params?: object; // passed to api request for GET, DELETE
  message?: string;
}
export interface ProcessState {
  steps: StepShape[];
  current: number;
  done?: boolean;
}

export interface AppItemShape {
  identity: string;
  name: string;
  version: string;
}
