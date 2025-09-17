export interface ProcessStepShape {
  title: string;
  dataSource: { apiUrl: string; apiMethod?: string };
  data?: object;
  message?: string;
}

export interface AppItemShape {
  installation_status: string;
  purchase_url: string;
  id: number;
  identity: string;
  name: string;
  description: string;
  type: string;
  price: string;
  pricing_type: string;
  renewal_fee: string;
  discount: string;
  is_featured: boolean;
  rated: number;
  total_reviews: number;
  total_rated: string;
  total_installed: number;
  version: string;
  version_detail: {
    release_channel?: string;
  };
  updated_at: string;
  created_at: string;
  compatible: string;
  mobile_support: boolean;
  mobile_compatible: string;
  demo_url: string;
  term_url: string;
  has_processing_payment?: boolean;
  author: {
    name: string;
    url: string;
    rated: number;
    total_rated: string;
  };
  categories: any[];
  url: string;
  image: {
    origin: string;
    '1024': string;
    '500': string;
  };
  user: {
    name: string;
    url: string;
  };
  can_install: boolean;
  can_purchase: boolean;
  required: boolean;
  downloaded: boolean;
}

export interface RequireItem {
  label: string;
  value: boolean;
  text: string;
  severity: 'error' | 'warning' | 'info';
  skip?: boolean;
}

export interface RequireSection {
  title: string;
  items: RequireItem[];
}

export interface Requirement {
  sections: RequireSection[];
  result: boolean;
}

export interface AppState {
  failed?: boolean;
  debug: boolean;
  baseUrl: string;
  results: {
    text?: string;
    component?: 'text' | 'alert' | undefined;
    props: Record<string, any>;
  }[];
  succeed?: boolean;
  license?: {
    id: string;
    key: string;
  };
  database?: {
    host: string;
    port: number;
    name: string;
    prefix: string;
    user: string;
    password: string;
  };
  administrator?: {
    email: string;
    username: string;
    password: string;
  };
  general: {
    site_name: string;
    app_url: string;
    frontend_url: string;
    app_env: 'production' | 'local';
    app_key: string;
    app_debug: boolean;
  };
  /**
   * Apps vendor by MetaFox but core
   */
  recommendAppsLoaded: boolean;
  recommendApps: AppItemShape[];
  selectedApps: {
    identity: string;
    name: string;
    version: string;
    release_channel: string;
  }[];
  steps: { id: string; title: string }[];
  processList: ProcessStepShape[];
  disabledStep: boolean;
  currentStep: number;
  forceStep?: string;
  verifiedStep: number;
  platformVersion: string;
  helpBlock: string;
  requirement: Requirement;
}
