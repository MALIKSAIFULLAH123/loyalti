/**
 * @type: layoutBlockFeature
 * name: dataSource
 */
import { BlockFeatureCreatorConfig } from '@metafox/layout/types';

export default function blockProps({ disabled }: BlockFeatureCreatorConfig) {
  return {
    component: 'CollapseContainer',
    label: 'Data Source',
    name: 'dataSource',
    elements: {
      apiUrl: {
        name: 'dataSource.apiUrl',
        component: 'Text',
        variant: 'outlined',
        maxLength: 100,
        label: 'apiUrl',
        labelProps: { shrink: true },
        fullWidth: true,
        disabled: disabled['dataSource.apiUrl'],
        margin: 'dense'
      },
      apiParams: {
        name: 'dataSource.apiParams',
        component: 'Text',
        variant: 'outlined',
        labelProps: { shrink: true },
        label: 'Sort & Filter',
        margin: 'dense',
        fullWidth: true,
        disabled: disabled['dataSource.apiParams']
      }
    }
  };
}
