import { get, isString } from 'lodash';
import * as yup from 'yup';
import { NumberSchema } from 'yup';
import { withTypeError } from '../../toYup';
import { NumberTypeSchema } from '../../types';
import { refValue, refValues } from '../shared';
import withWhen from '../withWhen';
interface TestContextExtended {
  originalValue?: unknown;
}
yup.addMethod(yup.number, 'leadingZero', function isValid(message) {
  return this.test('no-leading-zero', message, (value, context) => {
    const { originalValue } = context as yup.TestContext & TestContextExtended;

    if (isString(originalValue)) {
      // float number or zero
      if (originalValue?.startsWith('0.') || originalValue === '0') {
        return true;
      }

      return !originalValue?.startsWith('0');
    }

    return true;
  });
});

const toYupNumber = (
  jsonSchema: NumberTypeSchema,
  forceRequired?: boolean
): NumberSchema => {
  let yupSchema = yup.number();

  // default will check leadingZero (undefined, true)
  if (jsonSchema.leadingZero !== false) {
    yupSchema = yupSchema.leadingZero(
      jsonSchema.errors?.leadingZero ||
        jsonSchema.errors?.typeError ||
        'Leading zero is not allowed'
    );
  }

  /* istanbul ignore next */
  if (jsonSchema.round !== undefined) {
    yupSchema = withRound(yupSchema, jsonSchema);
  }

  if (jsonSchema.label) {
    yupSchema = yupSchema.label(jsonSchema.label);
  }

  const { errors = {} } = jsonSchema;

  ['lessThan', 'moreThan'].forEach(compare => {
    if (jsonSchema[compare] !== undefined) {
      yupSchema = yupSchema[compare](
        refValue(jsonSchema[compare]),
        get(errors, compare)
      );
    }
  });

  if (jsonSchema.sign === 'positive') {
    yupSchema = withPositive(yupSchema, jsonSchema);
  }

  if (jsonSchema.sign === 'negative') {
    yupSchema = withNegative(yupSchema, jsonSchema);
  }

  if (jsonSchema.min !== undefined) {
    yupSchema = yupSchema.min(refValue(jsonSchema['min']), errors.min);
  }

  if (jsonSchema.max !== undefined) {
    yupSchema = yupSchema.max(refValue(jsonSchema['max']), errors.max);
  }

  if (jsonSchema.integer === true) {
    yupSchema = withInteger(yupSchema, jsonSchema);
  }

  if (Array.isArray(jsonSchema.oneOf)) {
    yupSchema = withOneOf(yupSchema, jsonSchema);
  }

  if (Array.isArray(jsonSchema.notOneOf)) {
    yupSchema = withNotOneOf(yupSchema, jsonSchema);
  }

  if (jsonSchema.nullable !== undefined) {
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

  if (jsonSchema.when) {
    yupSchema = withWhen(yupSchema, jsonSchema.when);
  }

  return yupSchema;
};

function withPositive(
  schema: NumberSchema,
  jsonSchema: NumberTypeSchema
): NumberSchema {
  return schema.positive(jsonSchema.errors?.positive);
}

function withNegative(
  schema: NumberSchema,
  jsonSchema: NumberTypeSchema
): NumberSchema {
  return schema.negative(jsonSchema.errors?.negative);
}

function withInteger(
  schema: NumberSchema,
  jsonSchema: NumberTypeSchema
): NumberSchema {
  return schema.integer(jsonSchema.errors?.integer);
}

function withOneOf(
  schema: NumberSchema,
  jsonSchema: NumberTypeSchema
): NumberSchema {
  return schema.oneOf(refValues(jsonSchema.oneOf), jsonSchema?.errors?.oneOf);
}

function withNotOneOf(
  schema: NumberSchema,
  jsonSchema: NumberTypeSchema
): NumberSchema {
  return schema.notOneOf(
    refValues(jsonSchema.notOneOf),
    jsonSchema?.errors?.notOneOf
  );
}

/* istanbul ignore next */
function withRound(
  schema: NumberSchema,
  jsonSchema: NumberTypeSchema
): NumberSchema {
  return schema.round(jsonSchema.round);
}

function withNullable(
  schema: NumberSchema,
  jsonSchema: NumberTypeSchema
): NumberSchema {
  return schema.nullable(jsonSchema.nullable as any);
}

function withRequired(
  schema: NumberSchema,
  jsonSchema: NumberTypeSchema
): NumberSchema {
  return schema.required(jsonSchema?.errors?.required);
}

function withStrict(
  schema: NumberSchema,
  jsonSchema: NumberTypeSchema
): NumberSchema {
  return schema.strict(jsonSchema.strict);
}

export default toYupNumber;
