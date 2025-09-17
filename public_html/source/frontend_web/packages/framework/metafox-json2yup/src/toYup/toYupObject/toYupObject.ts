import * as yup from 'yup';
import { AnyObjectSchema } from 'yup';
import { toYup } from '..';
import { BuildCustomSchema, ObjectTypeSchema } from '../../types';
import withWhen from '../withWhen';
import { withTypeError } from '../../toYup';

yup.addMethod(yup.object, 'uniqueBy', function isValid(propertyName, message) {
  return this.test('uniqueBy', message, (value, ctx) => {
    const { path } = ctx;
    const options = [...ctx.parent];
    const currentIndex = options.indexOf(value);

    const subOptions = options.slice(0, currentIndex);

    if (
      subOptions.some(
        option =>
          option &&
          option[propertyName] &&
          option[propertyName] === value[propertyName]
      )
    ) {
      return ctx.createError({
        path: `${path}.${propertyName}`,
        message
      });
    }

    return true;
  });
});

const toYupObject = (
  jsonSchema: ObjectTypeSchema,
  forceRequired?: boolean,
  builder?: BuildCustomSchema
): AnyObjectSchema => {
  const fields = {};

  const fieldNames = Object.keys(jsonSchema.properties);

  fieldNames.forEach(fieldName => {
    fields[fieldName] = toYup(
      jsonSchema.properties[fieldName],
      forceRequired,
      builder
    );
  });

  let yupSchema = yup.object(fields);

  if (jsonSchema.uniqueBy) {
    yupSchema = yupSchema.uniqueBy(
      jsonSchema.uniqueBy,
      jsonSchema.errors?.uniqueBy
    );
  }

  if (jsonSchema.required === true || forceRequired === true) {
    yupSchema = withRequired(yupSchema, jsonSchema);
  }

  if (jsonSchema.nullable !== undefined) {
    yupSchema = withNullable(yupSchema, jsonSchema);
  }

  if (jsonSchema.strict === true) {
    yupSchema = withStrict(yupSchema, jsonSchema);
  }

  if (jsonSchema.when) {
    yupSchema = withWhen(yupSchema, jsonSchema.when);
  }

  if (jsonSchema?.errors?.typeError != null) {
    yupSchema = withTypeError(yupSchema as any, jsonSchema);
  }

  return yupSchema;
};

function withStrict(
  schema: AnyObjectSchema,
  jsonSchema: ObjectTypeSchema
): AnyObjectSchema {
  return schema.strict(jsonSchema.strict);
}

function withNullable(
  schema: AnyObjectSchema,
  jsonSchema: ObjectTypeSchema
): AnyObjectSchema {
  return schema.nullable(jsonSchema.nullable as any);
}

function withRequired(
  schema: AnyObjectSchema,
  jsonSchema: ObjectTypeSchema
): AnyObjectSchema {
  return schema.required(jsonSchema?.errors?.required);
}

export default toYupObject;
