import { BlockFeatureCreatorConfig } from '@metafox/layout/types';

export default function blockProps({ disabled }: BlockFeatureCreatorConfig) {
  return {
    component: 'Checkbox',
    name: 'freeze',
    label: 'Freeze this block (this only work on fixed slot)',
    fullWidth: true,
    checkedValue: true,
    disabled: disabled['freeze']
  };
}
