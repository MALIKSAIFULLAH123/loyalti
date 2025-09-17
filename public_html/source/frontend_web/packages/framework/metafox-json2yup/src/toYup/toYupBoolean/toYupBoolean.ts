import * as yup from 'yup';
import { BooleanSchema } from 'yup';
import { withTypeError } from '../../toYup';
import { BooleanTypeSchema } from '../../types';
import withWhen from '../withWhen';

const toYupBoolean = (
  jsonSchema: BooleanTypeSchema,
  forceRequired?: boolean
): BooleanSchema => {
  let yupSchema = yup.boolean();

  if (jsonSchema.label) {
    yupSchema = yupSchema.label(jsonSchema.label);
  }

  if (Array.isArray(jsonSchema.oneOf)) {
    yupSchema = withOneOf(yupSchema, jsonSchema);
  }

  if (Array.isArray(jsonSchema.notOneOf)) {
    yupSchema = withNotOneOf(yupSchema, jsonSchema);
  }

  if (jsonSchema.nullable != null) {
    yupSchema = withNullable(yupSchema, jsonSchema);
  }

  if (jsonSchema.required === true || forceRequired === true) {
    yupSchema = withRequired(yupSchema, jsonSchema);
  }

  if (jsonSchema?.errors?.typeError != null) {
    yupSchema = withTypeError(yupSchema as any, jsonSchema);
  }

  if (jsonSchema.strict) {
    yupSchema = withStrict(yupSchema, jsonSchema);
  }

  yupSchema = withWhen(yupSchema, jsonSchema.when);

  return yupSchema;
};

function withOneOf(
  schema: BooleanSchema,
  jsonSchema: BooleanTypeSchema
): BooleanSchema {
  return schema.oneOf(jsonSchema.oneOf, jsonSchema?.errors?.oneOf);
}

function withNotOneOf(
  schema: BooleanSchema,
  jsonSchema: BooleanTypeSchema
): BooleanSchema {
  return schema.notOneOf(jsonSchema.notOneOf, jsonSchema?.errors?.notOneOf);
}

function withNullable(
  schema: BooleanSchema,
  jsonSchema: BooleanTypeSchema
): BooleanSchema {
  return schema.nullable(jsonSchema.nullable as any);
}

function withRequired(
  schema: BooleanSchema,
  jsonSchema: BooleanTypeSchema
): BooleanSchema {
  return schema.required(jsonSchema?.errors?.required);
}

function withStrict(
  schema: BooleanSchema,
  jsonSchema: BooleanTypeSchema
): BooleanSchema {
  return schema.strict(jsonSchema.strict);
}

export default toYupBoolean;
