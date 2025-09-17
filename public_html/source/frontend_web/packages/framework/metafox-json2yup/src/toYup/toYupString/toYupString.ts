import * as yup from 'yup';
import { StringSchema } from 'yup';
import { withTypeError } from '../../toYup';
import { StringTypeSchema } from '../../types';
import { refValue, refValues } from '../shared';
import withWhen from '../withWhen';
import { isArray } from 'lodash';

yup.addMethod(
  yup.string,
  'mutipleRegex',
  function isValid(arrRegex, message, excludeEmptyString) {
    return this.test('mutipleRegex', message, value => {
      if (excludeEmptyString && !value) return true;

      for (let i = 0; i < arrRegex.length; ++i) {
        const reg = new RegExp(arrRegex[i]);

        if (!reg.test(value)) {
          return false;
        }
      }

      return true;
    });
  }
);

const toYupString = (
  jsonSchema: StringTypeSchema,
  forceRequired?: boolean
): StringSchema => {
  let yupSchema = yup.string();

  if (jsonSchema.label) {
    yupSchema = yupSchema.label(jsonSchema.label);
  }

  if (jsonSchema.minLength !== undefined) {
    yupSchema = withMinLength(yupSchema, jsonSchema);
  }

  if (jsonSchema.maxLength !== undefined) {
    yupSchema = withMaxLength(yupSchema, jsonSchema);
  }

  if (jsonSchema.case === 'lowercase') {
    yupSchema = withLowercase(yupSchema, jsonSchema);
  }

  if (jsonSchema.case === 'uppercase') {
    yupSchema = withUppercase(yupSchema, jsonSchema);
  }

  if (jsonSchema.matches != null) {
    yupSchema = withMatches(yupSchema, jsonSchema);
  }

  if (jsonSchema.format === 'email') {
    yupSchema = withEmail(yupSchema, jsonSchema);
  }

  if (jsonSchema.format === 'url') {
    yupSchema = withUrl(yupSchema, jsonSchema);
  }

  if (Array.isArray(jsonSchema.oneOf)) {
    yupSchema = withOneOf(yupSchema, jsonSchema);
  }

  if (Array.isArray(jsonSchema.notOneOf)) {
    yupSchema = withNotOneOf(yupSchema, jsonSchema);
  }

  yupSchema = withNullable(yupSchema, jsonSchema);

  if (jsonSchema.required === true || forceRequired === true) {
    yupSchema = withRequired(yupSchema, jsonSchema);
  }

  if (jsonSchema?.errors?.typeError != null) {
    yupSchema = withTypeError(yupSchema as any, jsonSchema);
  }

  if (jsonSchema.strict) {
    yupSchema = withStrict(yupSchema, jsonSchema);
  }

  if (jsonSchema.when) {
    yupSchema = withWhen(yupSchema, jsonSchema.when);
  }

  return yupSchema;
};

function withMaxLength(
  schema: StringSchema,
  jsonSchema: StringTypeSchema
): StringSchema {
  return schema.max(
    refValue(jsonSchema.maxLength),
    jsonSchema?.errors?.maxLength
  );
}

function withMinLength(
  schema: StringSchema,
  jsonSchema: StringTypeSchema
): StringSchema {
  return schema.min(
    refValue(jsonSchema.minLength),
    jsonSchema?.errors?.minLength
  );
}

function withLowercase(
  schema: StringSchema,
  jsonSchema: StringTypeSchema
): StringSchema {
  return schema.lowercase(jsonSchema?.errors?.lowercase);
}

function withUppercase(
  schema: StringSchema,
  jsonSchema: StringTypeSchema
): StringSchema {
  return schema.uppercase(jsonSchema?.errors?.uppercase);
}

function withEmail(
  schema: StringSchema,
  jsonSchema: StringTypeSchema
): StringSchema {
  return schema.email(jsonSchema?.errors?.format || jsonSchema?.errors?.email);
}

function withUrl(
  schema: StringSchema,
  jsonSchema: StringTypeSchema
): StringSchema {
  return schema.url(jsonSchema?.errors?.format || jsonSchema?.errors?.url);
}

function withMatches(
  schema: StringSchema,
  jsonSchema: StringTypeSchema
): StringSchema {
  if (
    isArray(jsonSchema.matches.regex) &&
    jsonSchema.matches.regex.length > 0
  ) {
    return schema.mutipleRegex(
      jsonSchema.matches.regex,
      jsonSchema?.errors?.matches,
      jsonSchema.matches.excludeEmptyString || false
    );
  }

  return schema.matches(RegExp(jsonSchema.matches.regex), {
    message: jsonSchema?.errors?.matches,
    excludeEmptyString: jsonSchema.matches.excludeEmptyString || false
  });
}

function withOneOf(
  schema: StringSchema,
  jsonSchema: StringTypeSchema
): StringSchema {
  return schema.oneOf(refValues(jsonSchema.oneOf), jsonSchema?.errors?.oneOf);
}

function withNotOneOf(
  schema: StringSchema,
  jsonSchema: StringTypeSchema
): StringSchema {
  return schema.notOneOf(
    refValues(jsonSchema.notOneOf),
    jsonSchema?.errors?.notOneOf
  );
}

function withNullable(
  schema: StringSchema,
  jsonSchema: StringTypeSchema
): StringSchema {
  return schema.nullable((jsonSchema.nullable as any) ?? true);
}

function withRequired(
  schema: StringSchema,
  jsonSchema: StringTypeSchema
): StringSchema {
  return schema.required(jsonSchema.errors?.required);
}

function withStrict(
  schema: StringSchema,
  jsonSchema: StringTypeSchema
): StringSchema {
  return schema.strict(jsonSchema.strict);
}

export default toYupString;
