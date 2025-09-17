/**
 * @type: layoutBlockFeature
 * name: gridProps
 */
import { OptionItemShape } from '@metafox/framework';
import { BlockFeatureCreatorConfig } from '@metafox/layout/types';
import { range } from 'lodash';

const SpacingOptions: OptionItemShape[] = range(0, 5 / 0.125).map(x => ({
  label: `${8 * 0.125 * x}px`,
  value: x * 0.125
}));

const getGridVariantOptions = (jsxBackend: any, contentType: string) => {
  const options = [
    { label: 'Grid View', value: 'gridView' },
    { label: 'List View', value: 'listView' }
  ];

  if (!contentType) {
    return options;
  }

  // support pin or fix.

  const reg = new RegExp(`${contentType}.itemView.`, 'i');
  const itemViews = jsxBackend.find(x => reg.test(x));
  const hasPin = itemViews.find(x => x.endsWith('pinCard'));
  const hasCasual = itemViews.find(x => x.endsWith('casualCard'));

  return [
    hasPin
      ? {
          label: 'Pin',
          value: 'pinView'
        }
      : false,
    hasCasual
      ? {
          label: 'Casual',
          value: 'casualView'
        }
      : false
  ]
    .concat(options)
    .filter(Boolean);
};

export default function gridProps(props: BlockFeatureCreatorConfig) {
  const { manager, disabled, config, extra, theme } = props;

  const contentType =
    config.overrides?.contentType ??
    config.defaults?.contentType ??
    extra?.mainListing?.contentType;

  const labels = {
    xs: `Screen width < ${theme.breakpoints.values.sm}px`,
    sm: `Screen width < ${theme.breakpoints.values.md}px`,
    md: `Screen width < ${theme.breakpoints.values.lg}px`,
    lg: `Screen width < ${theme.breakpoints.values.xl}px`,
    xl: `Screen width >=${theme.breakpoints.values.xl}px`
  };

  const sizingOptions = [
    { label: '1 per row', value: 12 },
    { label: '2 per row', value: 6 },
    { label: '3 per row', value: 4 },
    { label: '4 per row', value: 3 },
    { label: '6 per row', value: 2 }
  ];

  const minWidth = 330;

  return {
    component: 'Container',
    name: 'gridProps',
    elements: {
      variant: {
        name: 'gridVariant',
        component: 'Dropdown',
        label: 'Styling',
        size: 'small',
        margin: 'normal',
        variant: 'outlined',
        defaultValue: 'grid',
        displayEmpty: false,
        fullWidth: true,
        disabled: disabled['gridItemProps.variant'],
        required: true,
        options: getGridVariantOptions(manager.jsxBackend, contentType)
      },
      spacing: {
        component: 'Container',
        name: 'spacing',
        variant: 'horizontal',
        elements: {
          columnSpacing: {
            name: 'gridContainerProps.columnSpacing',
            component: 'Dropdown',
            label: 'Horizontal Spacing',
            fullWidth: false,
            options: SpacingOptions,
            sx: { minWidth: 320 },
            defaultValue: 0,
            showWhen: ['neq', 'gridVariant', 'listView'],
            description: 'Horizontal spacing between items'
          },
          rowSpacing: {
            name: 'gridContainerProps.rowSpacing',
            component: 'Dropdown',
            fullWidth: false,
            label: 'Vertical Spacing',
            sx: { minWidth: 320 },
            defaultValue: 0,
            options: SpacingOptions,
            description: 'Vertical spacing between items'
          }
        }
      },
      sizing: {
        component: 'Container',
        name: 'SizingGroup',
        variant: 'horizontal',
        description: 'How many items per row',
        showWhen: ['eq', 'gridVariant', 'gridView'],
        elements: {
          xs: {
            component: 'Dropdown',
            name: 'gridItemProps.xs',
            label: labels.xs,
            sx: { minWidth },
            options: sizingOptions
          },
          sm: {
            component: 'Dropdown',
            name: 'gridItemProps.sm',
            label: labels.sm,
            sx: { minWidth },
            options: sizingOptions
          },
          md: {
            component: 'Dropdown',
            name: 'gridItemProps.md',
            label: labels.md,
            sx: { minWidth },
            options: sizingOptions
          },
          large: {
            component: 'Dropdown',
            name: 'gridItemProps.lg',
            label: labels.lg,
            sx: { minWidth },
            options: sizingOptions
          },
          xl: {
            component: 'Dropdown',
            name: 'gridItemProps.xl',
            label: labels.xl,
            sx: { minWidth },
            options: sizingOptions
          }
        }
      }
    }
  };
}
