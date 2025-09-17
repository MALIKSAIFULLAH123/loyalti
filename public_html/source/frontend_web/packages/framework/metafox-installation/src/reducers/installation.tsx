/**
 * @type: reducer
 * name: installation
 * bundle: installation
 */

import produce from 'immer';
import { isPlainObject } from 'lodash';

export default produce(
  (draft, action) => {
    switch (action.type) {
      case '@install/update': {
        const data = action.payload;

        if (!isPlainObject(data)) return;

        Object.keys(data).forEach(name => {
          draft[name] = data[name];
        });
        break;
      }
    }
  },
  {
    debug: false,
    baseUrl: '',
    license: {},
    database: {},
    selectedApps: [],
    recommendAppsLoaded: false,
    recommendApps: [],
    processList: [],
    administrator: {
      username: 'admin'
    },
    general: {
      site_name: 'MetaFox'
    },
    steps: [],
    results: [],
    platformVersion: '',
    currentStep: 0,
    verifiedStep: 0
  }
);
