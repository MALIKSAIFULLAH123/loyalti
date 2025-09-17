import { SchemaOf } from 'yup';
import { WhenSchema, YupTypeSchema } from '../../types';
import { toYup } from '../index';

const withWhen = <T extends YupTypeSchema, U extends SchemaOf<any>>(
  schema: any,
  jsonSchema: WhenSchema<T>[]
): U => {
  if (Array.isArray(jsonSchema) && jsonSchema.length > 0) {
    jsonSchema.forEach(({ fields, is, then, otherwise }) => {
      if (is === '$exists') {
        is = (x: any): boolean => !!x;
      } else if (is === '$empty') {
        is = (x: any): boolean => !x;
      }

      const isCached = is;
      const thenCached = then;
      const otherwiseCached = otherwise;

      if (is === '$options') {
        schema = schema.when(fields, (value, currentSchema) => {
          if (value && then[value]) {
            return toYup(then[value]);
          }

          if (otherwiseCached != null) {
            return toYup(otherwiseCached);
          }

          return currentSchema;
        });
      } else if (
        is != null &&
        typeof is === 'object' &&
        // eslint-disable-next-line no-prototype-builtins
        is?.hasOwnProperty('type')
      ) {
        // probably a yup type schema
        schema = schema.when(fields, (value, currentSchema) => {
          if (toYup(isCached as YupTypeSchema).isValidSync(value)) {
            return toYup(thenCached);
          }

          if (otherwiseCached != null) {
            return toYup(otherwiseCached);
          }

          return currentSchema;
        });
      } else {
        schema = schema.when(fields, {
          is,
          then: toYup(then),
          otherwise: otherwise != null ? toYup(otherwise) : undefined
        });
      }
    });
  }

  return schema;
};

export default withWhen;
