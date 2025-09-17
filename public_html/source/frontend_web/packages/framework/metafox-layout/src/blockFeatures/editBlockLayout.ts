/**
 * @type: layoutBlockFeature
 * name: editBlockLayout
 */

import { BlockFeatureCreatorConfig } from '@metafox/layout/types';
import {
  selectBackgroundColorField,
  selectBorderRadiusField,
  selectDividerField,
  selectHeightField,
  selectMarginField,
  selectPaddingField,
  selectTextColorField,
  selectTypographyComponentField,
  selectTypographyVariantField
} from '@metafox/layout/utils';

export default function featureCreator({
  disabled,
  theme
}: BlockFeatureCreatorConfig) {
  return {
    component: 'Container',
    elements: {
      freeze: {
        component: 'Checkbox',
        name: 'freeze',
        label: 'Freeze this block (this only work on fixed slot)',
        fullWidth: true,
        checkedValue: true,
        disabled: disabled['freeze']
      },
      noHeader: {
        component: 'Checkbox',
        name: 'noHeader',
        label: "Don't show header",
        fullWidth: true,
        checkedValue: true,
        disabled: disabled['noHeader']
      },
      noFooter: {
        component: 'Checkbox',
        name: 'noFooter',
        label: "Don't show footer",
        fullWidth: true,
        checkedValue: true
      },
      blockStyle: {
        component: 'CollapseContainer',
        label: 'Container',
        description: 'Styling for block container',
        elements: {
          bgColor: selectBackgroundColorField(theme, {
            name: 'blockProps.blockStyle.sx.bgcolor',
            fullWidth: true
          }),
          borderRadius: selectBorderRadiusField(theme, {
            name: 'blockProps.blockStyle.sx.borderRadius',
            fullWidth: true
          }),
          gutter: selectPaddingField(theme, {
            prefix: 'blockProps.blockStyle.sx',
            description:
              "Gutter - space between block's border and the content."
          }),
          margin: selectMarginField(theme, {
            prefix: 'blockProps.blockStyle.sx',
            description: "Margin - space outside block's border."
          }),
          maxWidth: {
            component: 'Dropdown',
            name: 'blockStyle.maxWidth',
            label: 'Max Width - Determine the max-width of the block',
            description: 'The block width grows with the size of the screen.',
            defaultValue: 'none',
            fullWidth: true,
            margin: 'normal',
            options: [
              { label: '100%', value: '' },
              { label: 'xs', value: theme.breakpoints.values['xs'] },
              { label: 'sm', value: theme.breakpoints.values['sm'] },
              { label: 'md', value: theme.breakpoints.values['md'] },
              { label: 'lg', value: theme.breakpoints.values['lg'] }
            ]
          }
        }
      },
      contentStyle: {
        component: 'CollapseContainer',
        label: 'Content',
        elements: {
          bgcolor: selectBackgroundColorField(theme, {
            name: 'blockProps.contentStyle.sx.bgcolor'
          }),
          borderRadius: selectBorderRadiusField(theme, {
            name: 'blockProps.contentStyle.sx.borderRadius'
          }),
          gutter: selectPaddingField(theme, {
            description: "Gutter - space inside block's content",
            prefix: 'blockProps.contentStyle.sx'
          }),
          margin: selectMarginField(theme, {
            description: "Margin - space outside block's content",
            prefix: 'blockProps.contentStyle.sx'
          })
        }
      },
      headerStyle: {
        component: 'CollapseContainer',
        label: 'Header',
        showWhen: ['falsy', 'noHeader'],
        variant: 'horizontal',
        elements: {
          height: selectHeightField(theme, {
            name: 'blockProps.headerStyle.sx.height'
          }),
          component: selectTypographyComponentField(theme, {
            name: 'blockProps.titleStyle.component',
            label: 'Title Component',
            defaultValue: 'h2',
            fullWidth: false,
            sx: { minWidth: 220 }
          }),
          typography: selectTypographyVariantField(theme, {
            name: 'blockProps.titleStyle.sx.typography',
            label: 'Typography',
            fullWidth: false,
            sx: { minWidth: 220 }
          }),
          color: selectTextColorField(theme, {
            name: 'blockProps.titleStyle.sx.color',
            fullWidth: false,
            sx: { minWidth: 220 }
          }),
          divider: selectDividerField(theme, {
            label: 'Title Divider',
            name: 'blockProps.headerStyle.dividerVariant',
            fullWidth: false,
            sx: { minWidth: 220 }
          }),
          gutter: selectPaddingField(theme, {
            description: "Gutter - space between block's header and title.",
            prefix: 'blockProps.headerStyle.sx',
            fullWidth: false,
            sx: { minWidth: 220 }
          }),
          margin: selectMarginField(theme, {
            description:
              "Margin - space between block's header and block container.",
            prefix: 'blockProps.headerStyle.sx',
            fullWidth: false,
            sx: { minWidth: 220 }
          })
        }
      },
      footerStyle: {
        component: 'CollapseContainer',
        label: 'Footer',
        showWhen: ['falsy', 'noFooter'],
        elements: {
          divider: selectDividerField(theme, {
            name: 'blockProps.footerStyle.dividerVariant'
          }),
          gutter: selectPaddingField(theme, {
            prefix: 'blockProps.footerStyle.sx',
            description: 'Gutter - Space inside footer'
          }),
          margin: selectMarginField(theme, {
            prefix: 'blockProps.footerStyle.sx',
            description: 'Margin - Space outside footer'
          })
        }
      }
    }
  };
}
