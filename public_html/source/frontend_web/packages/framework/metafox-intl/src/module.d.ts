import '@metafox/framework/Manager';
import { IntlShape } from 'react-intl';
import { AppState, Messages } from '@metafox/intl/types';
import React from 'react';
import useIntlMessages from '@metafox/intl/services/useIntlMessages';

declare module '@metafox/framework/Manager' {
  interface Manager {
    i18n: IntlShape;
    moment: any;
    IntlProvider?: React.FC;
    useIntlMessages(): Messages;
  }

  interface GlobalState {
    intl: AppState;
  }

  interface RootConfig {
    i18n?: any;
  }

  interface ManagerConfig {
    messages?: Record<string, string>;
  }
}
