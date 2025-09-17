import { isArray, isPlainObject, isString } from 'lodash';
import * as yup from 'yup';
import { SchemaOf as Schema } from 'yup';
import Reference from 'yup/lib/Reference';
import { YupTypeSchema } from '../../types';

export function withTypeError<T extends Schema<unknown>>(
  schema: T,
  jsonSchema: YupTypeSchema
): T {
  return schema.typeError(jsonSchema?.errors?.typeError) as any;
}

/**
 * return yup.ref() or scala value
 * @param spec - any
 * @returns
 */
export function refValue<T>(spec: any): T | Reference<T> {
  if (isPlainObject(spec)) {
    if (isString(spec.ref)) {
      return yup.ref(spec.ref, spec.options);
    } else if (isArray(spec.ref)) {
      return yup.ref(spec.ref[0], spec.ref[1]);
    }
  }

  return spec;
}

/**
 * return yup.ref() or scala value defined by json
 *
 * @param values - validate ref value
 * @returns
 */
export function refValues<T>(values: T[]): (T | Reference<T>)[] {
  return values.map(refValue) as any;
}
