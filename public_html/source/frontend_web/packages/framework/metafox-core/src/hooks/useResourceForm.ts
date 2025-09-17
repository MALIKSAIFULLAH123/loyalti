import { GlobalState } from '@metafox/framework/Manager';
import { FormSchemaShape } from '@metafox/form';
import { get } from 'lodash';
import { useSelector } from 'react-redux';
import { RemoteDataSource } from '..';

export default function useResourceForm(
  appName: string,
  resourceName: string,
  formName: string
): FormSchemaShape | undefined {
  return useSelector<GlobalState, FormSchemaShape | RemoteDataSource>(state =>
    get(state, `_forms.${appName}.${resourceName}.${formName}`)
  ) as any;
}
