/**
 * @type: block
 * name: user.block.AdminFormTabs
 * title: AdminCP - Form Tabs
 * bundle: admincp
 */

import { createBlock } from '@metafox/framework';
import Base from './Base';

export default createBlock({
  extendBlock: Base,
  defaults: {
    title: 'Admin Form',
    loadFrom: 'pageParams'
  },
  custom: {
    minHeight: {
      name: 'minHeight',
      component: 'Text',
      label: 'Min Height',
      variant: 'outlined',
      fullWidth: true
    },
    loadFrom: {
      name: 'loadFrom',
      component: 'radio',
      label: 'Load Form from Api',
      variant: 'outlined',
      required: true,
      fullWidth: true,
      options: [
        { value: 'pageParams', label: 'Configured' },
        { value: 'formName', label: 'Fill appName, form Name' },
        { value: 'apiUrl', label: 'Fill apiUrl directly' }
      ]
    },
    apiUrl: {
      name: 'dataSource.apiUrl',
      component: 'Text',
      label: 'API Url',
      variant: 'outlined',
      required: true,
      fullWidth: true,
      showWhen: ['eq', 'loadFrom', 'apiUrl']
    },
    appName: {
      name: 'appName',
      component: 'Text',
      label: 'AppName',
      variant: 'outlined',
      required: true,
      fullWidth: true,
      showWhen: ['eq', 'loadFrom', 'formName']
    },
    resourceName: {
      name: 'resourceName',
      component: 'Text',
      label: 'Resource',
      variant: 'outlined',
      required: true,
      fullWidth: true,
      showWhen: ['eq', 'loadFrom', 'formName']
    },
    formName: {
      name: 'formName',
      component: 'Text',
      label: 'Form Name',
      variant: 'outlined',
      required: true,
      fullWidth: true,
      showWhen: ['eq', 'loadFrom', 'formName']
    }
  }
});
