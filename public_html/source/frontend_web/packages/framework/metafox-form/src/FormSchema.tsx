import React from 'react';
import { FormSchemaShape } from '@metafox/form/types';

export const FormSchemaContext = React.createContext<FormSchemaShape>({
  name: 'Form',
  component: 'Form',
  type: 'container'
});

export const FormSchemaProvider = FormSchemaContext.Provider;

export const useFormSchema = () => React.useContext(FormSchemaContext);

export const withFormSchema = WrappedComponent => props =>
  (
    <FormSchemaContext.Consumer>
      {config => <WrappedComponent {...props} {...config} />}
    </FormSchemaContext.Consumer>
  );
