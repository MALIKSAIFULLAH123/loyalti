import { FormSchemaShape } from '@metafox/form';

export default function transformPropForm(
  map: Record<string, any[]>
): FormSchemaShape {
  const formSchema: FormSchemaShape = {
    component: 'Form',
    variant: 'horizontal',
    elements: {},
    value: {}
  };

  Object.keys(map).forEach(name => {
    const element = {
      component: 'Select',
      label: name,
      name,
      size: 'small',
      options: map[name].map(x => ({ label: x, value: x }))
    };
    formSchema.value[name] = map[name][0];
    formSchema.elements[name] = element;
  });

  return formSchema;
}
