/**
 * @type: layoutBlockFeature
 * name: editNoContentLayout
 */

import { BlockFeatureCreatorConfig } from '@metafox/layout/types';
import {
  selectAlginItemsField,
  selectBackgroundColorField,
  selectBorderRadiusField,
  selectFontWeightField,
  selectHeightField,
  selectJustifyContentField,
  selectMarginField,
  selectPaddingField,
  selectTextAlginField,
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
      noTitle: {
        component: 'Checkbox',
        name: 'noTitle',
        label: 'Disable Title',
        fullWidth: true,
        checkedValue: true,
        disabled: disabled['noMedia']
      },
      noMedia: {
        component: 'Checkbox',
        name: 'noMedia',
        label: 'Disable Media',
        fullWidth: true,
        checkedValue: true
      },
      noSummary: {
        component: 'Checkbox',
        name: 'noSummary',
        label: 'Disable Summary',
        fullWidth: true,
        checkedValue: true
      },
      contentStyle: {
        component: 'CollapseContainer',
        label: 'Content',
        open: true,
        elements: {
          bgcolor: selectBackgroundColorField(theme, {
            name: 'contentStyle.sx.bgcolor'
          }),
          borderRadius: selectBorderRadiusField(theme, {
            name: 'contentStyle.sx.borderRadius'
          }),
          alignItems: selectAlginItemsField(theme, {
            name: 'contentStyle.sx.alignItems'
          }),
          justifyContent: selectJustifyContentField(theme, {
            name: 'contentStyle.sx.justifyContent'
          }),
          gutter: selectPaddingField(theme, {
            prefix: 'contentStyle.sx',
            description: 'Gutter - Space inside content'
          }),
          margin: selectMarginField(theme, {
            prefix: 'contentStyle.sx',
            description: 'Margin - Space outside content'
          })
        }
      },
      mediaStyle: {
        component: 'CollapseContainer',
        label: 'Media',
        showWhen: ['falsy', 'noMedia'],
        elements: {
          height: selectHeightField(theme, {
            name: 'mediaStyle.sx.height'
          }),
          alignItems: selectAlginItemsField(theme, {
            name: 'mediaStyle.sx.alignItems'
          }),
          justifyContent: selectJustifyContentField(theme, {
            name: 'mediaStyle.sx.justifyContent'
          }),
          textAlign: selectTextAlginField(theme, {
            name: 'mediaStyle.sx.textAlign'
          }),
          gutter: selectPaddingField(theme, {
            prefix: 'mediaStyle.sx',
            description: 'Gutter - Space inside media'
          }),
          margin: selectMarginField(theme, {
            prefix: 'mediaStyle.sx',
            description: 'Margin - Space outside media'
          })
        }
      },
      textStyle: {
        component: 'CollapseContainer',
        label: 'Text',
        elements: {
          height: selectHeightField(theme, {
            name: 'textStyle.sx.height'
          }),
          alignItems: selectAlginItemsField(theme, {
            name: 'textStyle.sx.alignItems'
          }),
          justifyContent: selectJustifyContentField(theme, {
            name: 'textStyle.sx.justifyContent'
          }),
          textAlign: selectTextAlginField(theme, {
            name: 'textStyle.sx.textAlign'
          }),
          gutter: selectPaddingField(theme, {
            prefix: 'textStyle.sx',
            description: 'Gutter - Space inside text'
          }),
          margin: selectMarginField(theme, {
            prefix: 'textStyle.sx',
            description: 'Margin - Space outside text'
          })
        }
      },
      titleStyle: {
        component: 'CollapseContainer',
        label: 'Title',
        showWhen: ['falsy', 'noTitle'],
        elements: {
          component: selectTypographyComponentField(theme, {
            name: 'titleStyle.component',
            label: 'Title Component',
            defaultValue: 'h4'
          }),
          typography: selectTypographyVariantField(theme, {
            name: 'titleStyle.variant',
            label: 'Typography'
          }),
          alignItems: selectAlginItemsField(theme, {
            name: 'titleStyle.sx.alignItems'
          }),
          justifyContent: selectJustifyContentField(theme, {
            name: 'titleStyle.sx.justifyContent'
          }),
          textAlign: selectTextAlginField(theme, {
            name: 'titleStyle.sx.textAlign'
          }),
          color: selectTextColorField(theme, {
            name: 'titleStyle.sx.color'
          }),
          gutter: selectPaddingField(theme, {
            prefix: 'titleStyle.sx',
            description: 'Gutter - Space inside title'
          }),
          margin: selectMarginField(theme, {
            prefix: 'titleStyle.sx',
            description: 'Margin - Space outside title'
          })
        }
      },
      summaryStyle: {
        component: 'CollapseContainer',
        label: 'Summary',
        showWhen: ['falsy', 'noSummary'],
        elements: {
          component: selectTypographyComponentField(theme, {
            name: 'summaryStyle.component',
            label: 'Title Component',
            defaultValue: 'p'
          }),
          typography: selectTypographyVariantField(theme, {
            name: 'summaryStyle.variant',
            label: 'Typography',
            default: 'body1'
          }),
          color: selectTextColorField(theme, {
            name: 'summaryStyle.sx.color'
          }),
          fontWeight: selectFontWeightField(theme, {
            name: 'summaryStyle.sx.fontWeight'
          }),
          alignItems: selectAlginItemsField(theme, {
            name: 'summaryStyle.sx.alignItems'
          }),
          justifyContent: selectJustifyContentField(theme, {
            name: 'summaryStyle.sx.justifyContent'
          }),
          textAlign: selectTextAlginField(theme, {
            name: 'summaryStyle.sx.textAlign'
          }),
          gutter: selectPaddingField(theme, {
            prefix: 'summaryStyle.sx',
            description: 'Gutter - space inside summary'
          }),
          margin: selectMarginField(theme, {
            prefix: 'summaryStyle.sx',
            description: 'Margin - space outside summary'
          })
        }
      }
    }
  };
}
