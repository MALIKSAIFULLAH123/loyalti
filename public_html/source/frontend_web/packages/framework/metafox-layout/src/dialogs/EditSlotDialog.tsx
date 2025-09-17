/**
 * @type: dialog
 * name: layout.editSlotDialog
 * chunkName: layoutEditor
 */
import { useGlobal } from '@metafox/framework';
import { Dialog } from '@metafox/dialog';
import { FormBuilder, FormSchemaShape } from '@metafox/form';
import {
  getFlexWeightOptions,
  getSlotMaxContentWidthOptions,
  getSlotMaxWidthOptions,
  selectAlginContentField,
  selectBackgroundColorField,
  selectMarginField,
  selectPaddingField,
  selectSlotMinHeightField
} from '@metafox/layout/utils';
import { range } from 'lodash';
import React from 'react';

interface Props {
  templateName: string;
  slotName: string;
  elementPath: string;
}

export default function EditSlotDialog(props: Props) {
  const { slotName } = props;
  const { useDialog, i18n, layoutBackend, useTheme } = useGlobal();
  const { closeDialog, dialogProps } = useDialog();
  const config = layoutBackend.getSlotConfig(props);
  const theme = useTheme();
  const widthOptions = range(1, 12).map(x => ({
    value: x.toString(),
    label: `${x}/12 of row`
  }));

  config.slotName = slotName;

  const variantOptions = [
    ...[
      {
        value: 'layout.slot.LiveEdit',
        label: 'Default'
      },
      {
        value: 'layout.liveStickySlot',
        label: 'Sticky'
      },
      {
        value: 'layout.liveFixedSlot',
        label: 'liveFixedSlot - Fix height by remaining screen height'
      }
    ],
    ...(theme.slotOptions ?? [])
  ];

  const onSubmit = values => {
    layoutBackend.setSlotConfig(props, (data: any) => values);
    closeDialog();
  };

  const formSchema: FormSchemaShape = {
    component: 'Form',
    title: i18n.formatMessage({ id: 'edit_layout_slot' }),
    elements: {
      content: {
        component: 'Container',
        elements: {
          slot: {
            component: 'CollapseContainer',
            label: 'Container',
            open: true,
            elements: {
              slotName: {
                component: 'Hidden',
                name: 'slotName'
              },
              component: {
                name: 'component',
                component: 'Select',
                label: 'Scroll Behavior',
                variant: 'outlined',
                margin: 'normal',
                defaultValue: '',
                displayEmpty: false,
                fullWidth: true,
                options: variantOptions,
                showWhen: ['includes', 'slotName', ['side', 'subside']]
              },
              disabledScroll: {
                name: 'disabledScroll',
                component: 'Checkbox',
                label:
                  "Disable overflow-y scroll effect, it's helpful to children element self control.",
                color: 'primary',
                margin: 'normal',
                checkedValue: true,
                fullWidth: true,
                showWhen: ['eq', 'component', 'layout.liveFixedSlot']
              },
              themeName: {
                name: 'themeName',
                component: 'Select',
                label: 'Use child theme',
                variant: 'outlined',
                margin: 'normal',
                options: [
                  { label: 'none', value: '' },
                  { label: 'invert', value: 'invert' }
                ],
                fullWidth: true
              },
              flexWeight: {
                name: 'flexWeight',
                component: 'Select',
                label: 'Width by flex weight',
                variant: 'outlined',
                margin: 'normal',
                options: getFlexWeightOptions(),
                fullWidth: true
              },
              xs: {
                name: 'xs',
                component: 'Select',
                label: 'Width by percentage',
                variant: 'outlined',
                margin: 'normal',
                options: widthOptions,
                fullWidth: true,
                enabledWhen: ['eq', 'flexWeight', '0']
              },
              maxWidth: {
                name: 'rootStyle.maxWidth',
                component: 'Select',
                label: 'Maximum width',
                variant: 'outlined',
                margin: 'normal',
                options: getSlotMaxWidthOptions(theme),
                fullWidth: true
              },
              minWidth: {
                name: 'rootStyle.minWidth',
                component: 'Select',
                label: 'Minimum width',
                variant: 'outlined',
                margin: 'normal',
                options: getSlotMaxWidthOptions(theme),
                fullWidth: true
              }
            }
          },
          stage: {
            component: 'CollapseContainer',
            label: 'Stage',
            description:
              'Stage is direct children of the container and wrap slot content',
            elements: {
              minHeight: selectSlotMinHeightField(null, {
                name: 'stageStyle.minHeight'
              }),
              height: {
                name: 'stageStyle.sx.height',
                component: 'Dropdown',
                defaultValue: 'auto',
                label: 'Height',
                options: [
                  { label: 'auto', value: 'auto' },
                  { label: 'full height (100%)', value: '100%' }
                ]
              },
              gutter: selectPaddingField(null, {
                prefix: 'stageStyle.sx',
                description: 'Gutter - space inside stage'
              }),
              margin: selectMarginField(null, {
                prefix: 'stageStyle.sx',
                description: 'Margin - space outside stage'
              })
            }
          },
          content: {
            component: 'CollapseContainer',
            label: 'Content',
            elements: {
              maxWidth: {
                name: 'contentStyle.maxWidth',
                component: 'Dropdown',
                label: 'Maximum width of content',
                variant: 'outlined',
                margin: 'normal',
                defaultValue: 'none',
                displayEmpty: false,
                fullWidth: true,
                options: getSlotMaxContentWidthOptions(theme)
              },
              minWidth: {
                name: 'contentStyle.minWidth',
                component: 'Dropdown',
                label: 'Minimum width of content',
                variant: 'outlined',
                margin: 'normal',
                defaultValue: 'none',
                displayEmpty: false,
                fullWidth: true,
                options: getSlotMaxContentWidthOptions(theme)
              },
              height: {
                name: 'contentStyle.sx.height',
                component: 'Dropdown',
                defaultValue: 'auto',
                label: 'Height',
                options: [
                  { label: 'auto', value: 'auto' },
                  { label: 'full height (100%)', value: '100%' }
                ]
              },
              gutter: selectPaddingField(theme, {
                prefix: 'contentStyle.sx',
                description: 'Gutter - Space inside content'
              }),
              margin: selectMarginField(theme, {
                prefix: 'contentStyle.sx',
                description: 'Margin - Space outside content'
              }),
              alignContent: selectAlginContentField(null, {
                name: 'contentStyle.sx.alignContent'
              }),
              background: selectBackgroundColorField(theme, {
                name: 'contentStyle.sx.bgcolor'
              })
            }
          }
        }
      },
      footer: {
        component: 'FormFooter',
        elements: {
          submit: {
            type: 'submit',
            variant: 'contained',
            component: 'Submit',
            label: 'Save Changes',
            color: 'primary'
          },
          cancel: {
            component: 'Cancel',
            label: 'Cancel',
            color: 'primary',
            type: 'cancel'
          }
        }
      }
    }
  };

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <FormBuilder
        dialog
        initialValues={config}
        formSchema={formSchema}
        onSubmit={onSubmit}
      />
    </Dialog>
  );
}
