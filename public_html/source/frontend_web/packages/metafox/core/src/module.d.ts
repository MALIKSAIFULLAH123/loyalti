import {
  ListViewBlockProps,
  SessionState,
  SmartDataGridConfig
} from '@metafox/framework';
import '@metafox/framework/Manager';
import React from 'react';
import RecentSearch from './services/RecentSearch';
import { AppState, ItemDetailInteractionBaseProps } from './types';

declare module '@metafox/framework/Manager' {
  interface GlobalState {
    core?: AppState;
    session?: SessionState;
    _actions?: any;
    _resourceMenus?: any;
    _appMenus: any;
    _forms: any;
  }

  interface AppResource {
    appName?: string;
    resourceName?: string;
    dataGrids?: Record<string, SmartDataGridConfig>;
  }

  interface Manager {
    recentSearch?: RecentSearch;
    ItemDetailInteraction?: React.FC<ItemDetailInteractionBaseProps>;
    ItemDetailInteractionInModal?: React.FC<ItemDetailInteractionBaseProps>;
    ListView?: React.FC<ListViewBlockProps>;
  }

  interface ManagerConfig {
    chat?: Partial<ChatPlusConfig>;
  }
}
