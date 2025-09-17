import { merge } from 'lodash';
import { FormSchemaShape } from './types';

export default function transformSchema(
  data: any,
  values: Record<string, any>,
  dialog?: boolean
): FormSchemaShape {
  Object.freeze(data);
  const schema: FormSchemaShape = { ...data };

  const { header, footer, ...elements } = schema.elements || {};

  schema.dialog = dialog;
  schema.elements = {
    header: header ?? {
      component: 'FormHeader',
      elements: {},
      action: data.action
    },
    content: {
      component: 'FormContent',
      elements
    },
    footer: footer ?? {
      component: 'FormFooter',
      elements: {}
    }
  };

  if (data.value) {
    // this method may not work with array merge
    merge(values, schema.value, { ...values });
  }

  return schema;
}
