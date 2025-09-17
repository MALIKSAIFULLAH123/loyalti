/**
 * @type: layoutBlockFeature
 * name: itemLayout
 */
import { BlockFeatureCreatorConfig } from '@metafox/layout/types';

export default function blockProps({
  manager,
  disabled
}: BlockFeatureCreatorConfig) {
  const options = [{ label: 'none', value: 'No layout' }].concat(
    Object.keys(manager.layoutBackend.getItemPresets()).map(value => ({
      label: value,
      value
    }))
  );

  return {
    component: 'Select',
    name: 'itemLayout',
    fullWidth: true,
    margin: 'normal',
    label: 'Item Layout',
    defaultValue: 'none',
    placeholder: 'No layout',
    options
  };
}
