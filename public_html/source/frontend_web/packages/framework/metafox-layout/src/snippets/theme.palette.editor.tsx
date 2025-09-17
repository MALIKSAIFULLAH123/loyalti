/**
 * @type: theme.style.editor
 * name: palette
 * title: layout_color_palette
 */
import { FormBuilder, FormElementShape } from '@metafox/form';
import React from 'react';

const paletteContainer = (name: string, label: string) => {
  return {
    component: 'AccordionContainer',
    label,
    elements: {
      main: {
        name: `palette.${name}.main`,
        component: 'ColorPicker',
        paletteName: `palette.${name}`,
        variant: 'standard',
        required: true,
        label: 'Main',
        size: 'medium',
        margin: 'dense'
      },
      light: {
        name: `palette.${name}.light`,
        component: 'ColorPicker',
        variant: 'standard',
        required: true,
        label: 'Light',
        size: 'medium',
        margin: 'dense'
      },
      dark: {
        name: `palette.${name}.dark`,
        component: 'ColorPicker',
        variant: 'standard',
        required: true,
        label: 'Dark',
        size: 'medium',
        margin: 'dense'
      }
    }
  };
};

const paletteContainerGroup = () => {
  const items = {
    primary: {
      label: 'Primary'
    },
    error: {
      label: 'Error'
    },
    warning: {
      label: 'Warning'
    },
    info: {
      label: 'Info'
    },
    success: {
      label: 'Success'
    }
  };

  return Object.keys(items).reduce((acc, key) => {
    acc[key] = paletteContainer(key, items[key].label);

    return acc;
  }, {});
};

const formSchema: FormElementShape = {
  component: 'Form',
  title: 'Edit Styling',
  submitOnValueChanged: true,
  elements: {
    appBar: {
      component: 'AccordionContainer',
      label: 'Header',
      elements: {
        background: {
          name: 'palette.appBar',
          label: 'Background',
          component: 'ColorPicker',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        }
      }
    },
    background: {
      component: 'AccordionContainer',
      label: 'Background',
      elements: {
        paper: {
          name: 'palette.background.paper',
          label: 'Paper',
          component: 'ColorPicker',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        },
        default: {
          name: 'palette.background.default',
          label: 'Default',
          component: 'ColorPicker',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        },
        secondary: {
          name: 'palette.background.secondary',
          label: 'Secondary',
          component: 'ColorPicker',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        }
      }
    },
    text: {
      component: 'AccordionContainer',
      label: 'Text',
      elements: {
        primary: {
          name: 'palette.text.primary',
          label: 'Primary',
          component: 'ColorPicker',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        },
        secondary: {
          name: 'palette.text.secondary',
          label: 'Secondary',
          component: 'ColorPicker',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        },
        disabled: {
          name: 'palette.text.disabled',
          label: 'Disabled',
          component: 'ColorPicker',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        },
        hint: {
          name: 'palette.text.hint',
          label: 'Hint',
          component: 'ColorPicker',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        }
      }
    },
    divider: {
      component: 'AccordionContainer',
      label: 'Divider',
      elements: {
        color: {
          name: 'palette.divider',
          label: 'Color',
          component: 'ColorPicker',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        }
      }
    },
    border: {
      component: 'AccordionContainer',
      label: 'Border',
      elements: {
        primary: {
          name: 'palette.border.primary',
          label: 'Primary',
          component: 'ColorPicker',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        },
        secondary: {
          name: 'palette.border.secondary',
          label: 'Secondary',
          component: 'ColorPicker',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        },
        outlined: {
          name: 'palette.border.outlined',
          label: 'Outlined',
          component: 'ColorPicker',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        }
      }
    },
    ...paletteContainerGroup()
  }
};

export default function Palette(props: any) {
  return <FormBuilder noHeader formSchema={formSchema} {...props} />;
}
