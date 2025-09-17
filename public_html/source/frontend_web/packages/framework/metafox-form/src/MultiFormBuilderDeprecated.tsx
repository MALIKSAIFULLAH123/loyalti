import React from 'react';
import FormBuilder from './FormBuilder';
import RemoteFormBuilder from './RemoteFormBuilder';
import { FormBuilderProps, RemoteFormBuilderProps } from './types';

export default function SmartFormBuilder(
  props: RemoteFormBuilderProps | FormBuilderProps
) {
  if ((props as FormBuilderProps).formSchema?.elements) {
    return <FormBuilder {...(props as FormBuilderProps)} />;
  }

  return <RemoteFormBuilder {...props} />;
}
