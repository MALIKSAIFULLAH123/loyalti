/**
 * @type: layoutBlockFeature
 * name: blockLayout
 */
import { BlockFeatureCreatorConfig } from '@metafox/layout/types';

export default function blockProps({
  manager,
  disabled
}: BlockFeatureCreatorConfig) {
  const options = [{ label: 'none', value: 'No Style' }].concat(
    Object.keys(manager.layoutBackend.getBlockPresets())
      .sort()
      .map(value => ({
        label: value,
        value
      }))
  );

  return {
    component: 'Container',
    elements: {
      blockLayout: {
        component: 'Select',
        name: 'blockLayout',
        fullWidth: true,
        margin: 'normal',
        label: 'Block Style',
        disabled: !!disabled['blockLayout'],
        options
      },
      editBlockLayout: {
        component: 'RelativeButton',
        name: 'editBlockLayout',
        showWhen: [
          'and',
          ['neq', 'blockLayout', 'none'],
          ['truthy', 'blockLayout']
        ],
        size: 'small',
        label: 'Edit Block Style',
        action: '@layout/editBlockLayoutPressed'
      }
    }
  };
}
