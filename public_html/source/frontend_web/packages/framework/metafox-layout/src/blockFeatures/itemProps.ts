/**
 * @type: layoutBlockFeature
 * name: itemProps
 */
import { BlockFeatureCreatorConfig } from '@metafox/layout/types';
import {
  selectAlginItemsField,
  selectBackgroundColorField,
  selectJustifyContentField
} from '../utils';

const marginField = (name: string, overrides: object = {}) => ({
  prefix: name,
  component: 'MarginSpaceGroup',
  size: 'small',
  margin: 'normal',
  ...overrides
});

const paddingField = (name: string, overrides: object = {}) => ({
  prefix: name,
  component: 'PaddingSpaceGroup',
  size: 'small',
  margin: 'normal',
  ...overrides
});

export default function blockProps({
  manager,
  disabled,
  theme,
  config,
  extra
}: BlockFeatureCreatorConfig) {
  return {
    component: 'Container',
    name: 'itemProps',
    elements: {
      variant: {
        name: 'itemProps.variant',
        component: 'Dropdown',
        label: 'Container Style',
        size: 'small',
        margin: 'normal',
        variant: 'outlined',
        defaultValue: 'containedCard',
        fullWidth: true,
        displayEmpty: true,
        options: [
          { label: 'Contained Card', value: 'containedCard' },
          { label: 'Flatted Card', value: 'flattedCard' },
          { label: 'Contained', value: 'contained' },
          { label: 'Flatted', value: 'flatted' }
        ]
      },
      content: {
        component: 'CollapseContainer',
        label: 'Content',
        elements: {
          divider: {
            name: 'itemProps.content.dividerVariant',
            component: 'Dropdown',
            label: 'Separator',
            size: 'small',
            margin: 'normal',
            variant: 'outlined',
            defaultValue: 'none',
            fullWidth: true,
            displayEmpty: true,
            description: 'Separator border between items',
            options: [
              { label: 'No', value: 'none' },
              { label: 'Border Top', value: 'top' },
              { label: 'Border Bottom', value: 'bottom' }
            ]
          },
          hover: {
            name: 'itemProps.content.hoverVariant',
            component: 'Dropdown',
            label: 'Hover',
            size: 'small',
            margin: 'normal',
            variant: 'outlined',
            defaultValue: 'none',
            fullWidth: true,
            displayEmpty: true,
            description: 'Highlight when mouse hover',
            options: [
              { label: 'No', value: 'none' },
              { label: 'Change background color', value: 'background' }
            ]
          },
          selected: {
            name: 'itemProps.content.selectedVariant',
            component: 'Dropdown',
            label: 'Item Selected',
            size: 'small',
            margin: 'normal',
            variant: 'outlined',
            defaultValue: 'none',
            fullWidth: true,
            displayEmpty: true,
            description: 'Highlight when item is selected',
            options: [
              { label: 'No', value: 'none' },
              { label: 'Change background', value: 'background' }
            ]
          },
          alignItems: selectAlginItemsField(theme, {
            name: 'itemProps.content.sx.alignItems'
          }),
          justifyContent: selectJustifyContentField(theme, {
            name: 'itemProps.content.sx.justifyContent'
          }),
          backgroundColor: selectBackgroundColorField(theme, {
            name: 'itemProps.content.sx.bgcolor'
          }),
          borderRadius: {
            name: 'itemProps.content.sx.borderRadius',
            component: 'Dropdown',
            label: 'Border Radius',
            fullWidth: true,
            options: [
              { value: 'none', label: 'No' },
              { value: 0.5, label: '4px' },
              { value: 1, label: '8px' },
              { value: 1.5, label: '12px' },
              { value: 2, label: '16px' }
            ]
          },
          border: {
            label: 'Border',
            fullWidth: true,
            name: 'itemProps.content.sx',
            component: 'BorderStyleGroup'
          },
          padding: paddingField('itemProps.content.sx', {
            description: 'Gutter - spacing inside content'
          }),
          margin: marginField('itemProps.content.sx', {
            description: 'Margin - spacing outside content'
          }),
          minHeight: {
            name: 'itemProps.content.minHeight',
            component: 'Text',
            label: 'Min Height',
            margin: 'normal',
            variant: 'outlined',
            fullWidth: true
          },
          showActionMenu: {
            name: 'itemProps.showActionMenu',
            component: 'Checkbox',
            label: 'Show Action Menu?',
            margin: 'normal',
            fullWidth: true,
            disabled: disabled['itemProps.showActionMenu'],
            showWhen: ['falsy', 'itemProps']
          }
        }
      },
      title: {
        component: 'CollapseContainer',
        label: 'Title',
        elements: {
          variant: {
            name: 'itemProps.title.variant',
            component: 'Dropdown',
            label: 'Title Variant',
            size: 'small',
            margin: 'normal',
            variant: 'outlined',
            defaultValue: 'h4',
            fullWidth: true,
            displayEmpty: true,
            options: [
              { label: 'H3', value: 'h3' },
              { label: 'H4', value: 'h4' },
              { label: 'H5', value: 'h5' },
              { label: 'H6', value: 'h6' }
            ]
          },
          lines: {
            name: 'itemProps.title.lines',
            component: 'Dropdown',
            label: 'Limit Lines',
            size: 'small',
            fullWidth: true,
            margin: 'normal',
            defaultValue: 'none',
            options: [
              { label: 'Unlimited', value: 'none' },
              { label: '1 line', value: 1 },
              { label: '2 lines', value: 2 },
              { label: '3 lines', value: 3 }
            ]
          },
          fixHeight: {
            name: 'itemProps.title.fixHeight',
            component: 'Checkbox',
            label: 'Fixed Height',
            margin: 'normal',
            fullWidth: true,
            defaultValue: 0,
            disabled: disabled['itemProps.title.fixHeight'],
            showWhen: [
              'and',
              ['truthy', 'itemProps.title.lines'],
              ['noneOf', 'itemProps.title.lines', [0, 'none', '']]
            ]
          },
          margin: marginField('itemProps.title.sx', {
            description: 'Margin - Spacing around title'
          })
        }
      },
      text: {
        component: 'CollapseContainer',
        label: 'Text',
        elements: {
          padding: paddingField('itemProps.text.sx', {
            description: 'Gutter - spacing outside text'
          }),
          margin: marginField('itemProps.text.sx', {
            description: 'Gutter - spacing around text'
          })
        }
      },
      summary: {
        component: 'CollapseContainer',
        label: 'Summary',
        elements: {
          component: {
            name: 'itemProps.summary.component',
            component: 'Dropdown',
            label: 'Summary Component',
            size: 'small',
            margin: 'normal',
            variant: 'outlined',
            defaultValue: 'div',
            fullWidth: true,
            displayEmpty: true,
            options: [
              { label: 'Block', value: 'div' },
              { label: 'Inline', value: 'span' },
              { label: 'Paragraph', value: 'p' }
            ]
          },
          variant: {
            name: 'itemProps.summary.variant',
            component: 'Dropdown',
            label: 'Variant',
            size: 'small',
            margin: 'normal',
            variant: 'outlined',
            defaultValue: 'body1',
            fullWidth: true,
            displayEmpty: true,
            options: [
              { label: 'h3', value: 'h3' },
              { label: 'h4', value: 'h4' },
              { label: 'h5', value: 'h5' },
              { label: 'h6', value: 'h6' },
              { label: 'body1', value: 'body1' },
              { label: 'body2', value: 'body2' }
            ]
          },
          color: {
            name: 'itemProps.summary.color',
            component: 'Dropdown',
            label: 'Summary Text Color',
            size: 'small',
            margin: 'normal',
            variant: 'outlined',
            defaultValue: 'textSecondary',
            fullWidth: true,
            displayEmpty: true,
            options: [
              { label: 'Primary', value: 'textPrimary' },
              { label: 'Secondary', value: 'textSecondary' },
              { label: 'Disabled', value: 'textDisabled' },
              { label: 'Hint', value: 'textHint' }
            ]
          },
          lines: {
            name: 'itemProps.summary.lines',
            component: 'Dropdown',
            label: 'Limit Lines',
            size: 'small',
            fullWidth: true,
            margin: 'normal',
            defaultValue: 'none',
            options: [
              { label: 'Unlimited', value: 'none' },
              { label: '1 line', value: 1 },
              { label: '2 lines', value: 2 },
              { label: '3 lines', value: 3 }
            ]
          },
          fixHeight: {
            name: 'itemProps.summary.fixHeight',
            component: 'Checkbox',
            label: 'Fixed Height',
            margin: 'normal',
            fullWidth: true,
            defaultValue: 0,
            disabled: disabled['itemProps.summary.fixHeight'],
            showWhen: ['neq', 'itemProps.summary.lines', 0]
          },
          margin: marginField('itemProps.summary.sx', {
            description: 'Margin - spacing around summary'
          })
        }
      },
      media: {
        component: 'CollapseContainer',
        label: 'Media',
        elements: {
          placement: {
            name: 'itemProps.media.placement',
            component: 'Dropdown',
            label: 'Placement',
            size: 'small',
            margin: 'normal',
            variant: 'outlined',
            defaultValue: '',
            fullWidth: true,
            displayEmpty: true,
            options: [
              { label: 'Start', value: 'start' },
              { label: 'End', value: 'end' },
              { label: 'Top', value: 'top' },
              { label: 'Bottom', value: 'bottom' },
              { label: 'No', value: 'none' }
            ]
          },
          mediaRatio: {
            name: 'itemProps.media.aspectRatio',
            component: 'Dropdown',
            label: 'Media Ratio',
            size: 'small',
            margin: 'normal',
            variant: 'outlined',
            defaultValue: '11',
            fullWidth: true,
            displayEmpty: true,
            options: [
              { label: '1/1', value: '11' },
              { label: '16/9', value: '169' },
              { label: '3/2', value: '32' },
              { label: '2/3', value: '23' },
              { label: '4/3', value: '43' },
              { label: '3/4', value: '34' }
            ]
          },
          width: {
            name: 'itemProps.media.width',
            component: 'Dropdown',
            label: 'Media Width',
            size: 'small',
            margin: 'normal',
            variant: 'outlined',
            defaultValue: '11',
            fullWidth: true,
            displayEmpty: true,
            options: [
              { label: 'auto', value: 'auto' },
              { label: 'full width', value: '100%' },
              { label: '200px', value: '200' },
              { label: '160px', value: '160' },
              { label: '120px', value: '120' },
              { label: '80px', value: '80' },
              { label: '56px', value: '56' },
              { label: '48px', value: '48' },
              { label: '40px', value: '40' },
              { label: '32px', value: '32' }
            ]
          },
          padding: paddingField('itemProps.media.sx', {
            description: 'Gutter - spacing around media'
          }),
          margin: marginField('itemProps.media.sx', {
            description: 'Margin - spacing inside media'
          })
        }
      }
    }
  };
}
