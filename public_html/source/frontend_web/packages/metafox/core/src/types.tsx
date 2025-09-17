import { ItemReactionInformationShape } from '@metafox/ui';

export interface TItemBase {
  id: number;
  resource_name?: string;
  module_name?: string;
}

export interface CoreStatusShape {
  new_chat_message?: number;
  chat_message?: number;
  new_feed: number;
  new_friend_request: number;
  new_notification: number;
}

export type LayoutBlockViewPropsContext = {
  classes?: Record<string, string>;
  variant?: string;
  noHeader?: boolean;
};

export interface RemoteDataSource {
  apiUrl: string;
  apiParams?: string | Record<string, any>;
}

export type ItemDetailInteractionBaseProps = {
  identity: string;
  className?: string;
  handleAction?: (type: string, payload?: unknown) => void;
  state?: any;
  messageCommentStatistic?: string;
  forceHideCommentList?: boolean;
  forceHideReactionGroup?: boolean;
  loading?: boolean;
  borderBottom?: boolean;
  borderTop?: boolean;
  menuName?: string;
};

export type ItemDetailInteractionProps = {
  extra?: any;
  most_reactions_information?: Array<ItemReactionInformationShape>;
  like_phrase?: any;
  statistic?: any;
  user_reacted?: any;
  related_comments?: string[];
  hideListComment?: boolean;
  hideComposerInListComment?: boolean;
  dataSourceCommentStatistic?: RemoteDataSource;
  related_comments_statistic?: Record<string, any>;
  handleActionCommentStatistic?: () => void;
} & ItemDetailInteractionBaseProps;

export interface AdmincpAppItemShape {
  id: string;
  icon: string;
  name: string;
  link: string;
  directory: string;
  author: {
    name: string;
    link: string;
  };
  purchasedDate: string;
  version: string;
  latestVersion: string;
}
export interface AdmincpLanguageItemShape {
  id: string;
  icon: string;
  name: string;
  link: string;
  default?: boolean;
  author: {
    name: string;
    link: string;
  };
  purchasedDate: string;
  version: string;
  latestVersion: string;
}

export type AppState = {
  uiConfig: any;
  status: CoreStatusShape;
};

export interface StoreProductItemShape {
  installation_status: string;
  purchase_url: string;
  id: number;
  name: string;
  description: string;
  action_button: Record<string, any>;
  type: string;
  price: string;
  pricing_type: string;
  pricing_type_label: string;
  text_changelog: string;
  renewal_fee: string;
  text_installation: string;
  discount: string;
  is_featured: boolean;
  rated: number;
  total_reviews: number;
  total_rated: string;
  total_installed: number;
  version: string;
  latest_version: string;
  updated_at: string;
  created_at: string;
  compatible: string;
  current_version: string;
  mobile_support: boolean;
  mobile_compatible: string;
  demo_url: string;
  term_url: string;
  text_detail: string;
  label_install: string;
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
  icon: {
    origin: string;
    '1024': string;
    '500': string;
  };
  images: [
    {
      origin: string;
      '1024': string;
    }
  ];
  user: {
    name: string;
    url: string;
  };
  can_install: boolean;
  can_purchase: boolean;
}
