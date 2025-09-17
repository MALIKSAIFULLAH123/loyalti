import { FormBuilder } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import React from 'react';

const URL_REGEX =
  /((https?:\/\/(www\.)?)|(www\.))[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_+.~#?&//=]*)/;

export default function AddLinkToImage({ onSubmit, values, onClose }) {
  const { i18n } = useGlobal();

  const formSchema = {
    component: 'Form',
    validation: {
      type: 'object',
      properties: {
        link: {
          type: 'string',
          errors: {
            matches: i18n.formatMessage({ id: 'invalid_link_url' })
          },
          matches: {
            regex: URL_REGEX,
            excludeEmptyString: true
          },
          label: i18n.formatMessage({ id: 'link_url' })
        }
      }
    },
    elements: {
      link: {
        name: 'link',
        component: 'Text',
        label: i18n.formatMessage({ id: 'link_url' }),
        fullWidth: true,
        placeholder: i18n.formatMessage({ id: 'link_url' }),
        variant: 'outlined',
        size: 'small'
      },
      target: {
        name: 'target',
        component: 'Checkbox',
        label: i18n.formatMessage({ id: 'open_link_in_new_window' }),
        checkedValue: '_blank',
        uncheckedValue: '_self',
        fullWidth: true,
        variant: 'outlined',
        size: 'small'
      },
      footer: {
        name: '_footer',
        component: 'FormFooter',
        elements: {
          submit: {
            name: '_submit',
            component: 'Submit',
            label: i18n.formatMessage({ id: 'ok' }),
            variant: 'contained',
            color: 'primary'
          }
        }
      }
    }
  };

  return (
    <FormBuilder
      formSchema={formSchema}
      onSubmit={data => {
        onSubmit(data);
        onClose();
      }}
      initialValues={values}
    />
  );
}
