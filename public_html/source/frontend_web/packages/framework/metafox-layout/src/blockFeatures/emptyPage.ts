/**
 * @type: layoutBlockFeature
 * name: emptyPageProps
 */

import { BlockFeatureCreatorConfig } from '@metafox/layout/types';

const getEmptyPageOptions = (jsxBackend: any) => {
  const defaultOptions = [
    {
      value: 'hide',
      label: 'hide'
    }
  ];

  const emptyBlock = Object.keys(jsxBackend.data).filter((block: string) =>
    block.includes('no_')
  );

  const optionsBlock = emptyBlock.map(block => ({
    value: block,
    label: block
  }));

  return [...defaultOptions, ...optionsBlock];
};

export default function featureCreator({
  disabled,
  manager
}: BlockFeatureCreatorConfig) {
  const noContentOptions = [{ label: 'No layout', value: '' }].concat(
    Object.keys(manager.layoutBackend.getNoContentPresets())
      .sort()
      .map(value => ({
        label: value,
        value
      }))
  );

  return {
    component: 'CollapseContainer',
    label: 'No Contents',
    elements: {
      disableWrapper: {
        name: 'emptyPageProps.noBlock',
        component: 'Checkbox',
        label: 'No Block Wrap',
        margin: 'normal',
        size: 'medium',
        defaultValue: false,
        fullWidth: true,
        checkedValue: 1,
        uncheckedValue: 0
      },
      emptyPage: {
        name: 'emptyPage',
        component: 'Select',
        label: 'Empty Page',
        size: 'medium',
        margin: 'normal',
        variant: 'outlined',
        defaultValue: 'hide',
        displayEmpty: false,
        fullWidth: true,
        required: true,
        options: getEmptyPageOptions(manager.jsxBackend)
      },
      noContentLayout: {
        component: 'Select',
        name: 'noContentLayout',
        fullWidth: true,
        margin: 'normal',
        size: 'medium',
        label: 'No Content Layout',
        defaultValue: '',
        placeholder: 'No Content',
        options: noContentOptions,
        showWhen: ['eq', 'emptyPage', 'no_content']
      },
      iconSource: {
        name: 'emptyPageProps.icon',
        component: 'LineIconPicker',
        label: 'Icon Source',
        margin: 'normal',
        size: 'medium',
        fullWidth: true,
        variant: 'outlined',
        showWhen: ['neq', 'emptyPage', 'hide']
      },
      title: {
        component: 'Text',
        name: 'emptyPageProps.title',
        label: 'Title',
        placeholder: 'Title of Page Empty',
        variant: 'outlined',
        fullWidth: true,
        margin: 'normal',
        size: 'medium',
        showWhen: ['neq', 'emptyPage', 'hide']
      },
      image: {
        component: 'Text',
        name: 'emptyPageProps.image',
        label: 'Image',
        placeholder: 'ImageURL',
        variant: 'outlined',
        margin: 'normal',
        size: 'medium',
        fullWidth: true,
        showWhen: ['neq', 'emptyPage', 'hide']
      },
      description: {
        component: 'Text',
        name: 'emptyPageProps.description',
        label: 'Description',
        placeholder: 'Description ...',
        variant: 'outlined',
        fullWidth: true,
        margin: 'normal',
        size: 'medium',
        showWhen: ['neq', 'emptyPage', 'hide']
      }
    }
  };
}
