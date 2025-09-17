import { FormSchemaShape } from '@metafox/form';
import { BlockFeatureCreatorConfig, EditBlockMode } from '@metafox/layout';
import { BlockFeatureCreator } from '@metafox/layout/types';
import { get, set } from 'lodash';

const prefix = 'elements.content.elements.';

function getElementPath(name: string): string {
  return prefix + name.replace(/\./g, '.elements.');
}

function fixContainer(
  formSchema: FormSchemaShape,
  supportFeatures: Record<string, BlockFeatureCreator>,
  name: string,
  options: BlockFeatureCreatorConfig
): void {
  if (!/^\w+\./.test(name)) return;

  const sectionName = name.replace(/\.(\w+)$/, '');

  if (!sectionName) return;

  const containerPath = getElementPath(sectionName);

  if (!get(formSchema, containerPath)) {
    const creator = supportFeatures[sectionName];
    const container = creator
      ? creator(options)
      : {
          component: 'Container',
          name: sectionName,
          wrapAs: 'div',
          elements: {}
        };

    set(formSchema, containerPath, container);
  }
}

export function createFeatures(
  editMode: EditBlockMode,
  formSchema: FormSchemaShape,
  options: BlockFeatureCreatorConfig
): void {
  const { features } = options;

  const supportFeatures = options.manager.layoutBackend.getBlockFeatures();
  let editFeatures = [];
  switch (editMode) {
    case 'editGridLayout':
      editFeatures = ['gridProps', 'gridItemProps'];
      break;
    case 'editItemLayout':
      editFeatures = ['itemProps'];
      break;
    case 'editBlockLayout':
      editFeatures = ['editBlockLayout'];
      break;
    case 'editNoContentLayout':
      editFeatures = ['editNoContentLayout'];
      break;
    default:
      editFeatures = [
        'title',
        'order',
        'itemView',
        'blockLayout',
        'gridLayout',
        'dataSource',
        'pagingProps',
        'emptyPageProps',
        'extraListProps',
        'headerActions'
      ];
  }

  // check filtered features

  editFeatures.concat(features).forEach(name => {
    const path = getElementPath(name);
    fixContainer(formSchema, supportFeatures, name, options);

    const creator = supportFeatures[name];

    if (!creator) {
      return;
    }

    const element = creator(options);

    if (element) {
      set(formSchema, path, element);
    }
  });
}
