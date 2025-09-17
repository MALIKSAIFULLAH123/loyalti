/**
 * @type: layoutBlockFeature
 * name: itemView
 */
import { Manager, OptionItemShape } from '@metafox/framework';
import { BlockFeatureCreatorConfig } from '@metafox/layout/types';

const getItemViewOptions = (
  manager: Manager,
  contentType: string
): OptionItemShape[] => {
  if (!contentType) {
    return;
  }

  const prefix = new RegExp(`${contentType}.itemView.`, 'i');

  return manager.jsxBackend
    .find(name => prefix.test(name))
    .map(name => ({ label: name, value: name }));
};

export default function blockProps({
  manager,
  disabled,
  config,
  extra
}: BlockFeatureCreatorConfig) {
  const contentType =
    config.overrides?.contentType ??
    config.defaults?.contentType ??
    extra?.mainListing?.contentType;

  if (!contentType) {
    return null;
  }

  const options = getItemViewOptions(manager, contentType);

  if (!options || 1 > options.length) return false;

  return {
    component: 'Select',
    inline: true,
    name: 'itemView',
    margin: 'normal',
    label: 'Item View',
    fullWidth: true,
    autoWidth: false,
    variant: 'outlined',
    placeholder: 'Item View',
    disabled: disabled['itemView'],
    options
  };
}
