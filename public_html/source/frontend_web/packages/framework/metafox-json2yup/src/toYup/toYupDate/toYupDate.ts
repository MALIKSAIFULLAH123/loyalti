import { subMonths, startOfYear, endOfYear } from 'date-fns';
import * as yup from 'yup';
import { DateSchema } from 'yup';
import { valueToDate } from '../../lib/date';
import { withTypeError } from '../../toYup';
import { DateTypeSchema } from '../../types';
import withWhen from '../withWhen';
import { refValue } from '../shared';
import { get } from 'lodash';
import moment from 'moment';

const toYupDate = (
  jsonSchema: DateTypeSchema,
  forceRequired?: boolean
): DateSchema => {
  let yupSchema = yup.date();

  if (jsonSchema.inputFormat !== undefined) {
    yupSchema = yupSchema.transform((value, originalValue) => {
      if (typeof originalValue === 'string' && jsonSchema.inputFormat != null) {
        try {
          return moment(originalValue, jsonSchema.inputFormat).toDate();
        } catch (e) {
          return null;
        }
      }

      return value;
    });
  }

  if (jsonSchema.label) {
    yupSchema = yupSchema.label(jsonSchema.label);
  }

  if (jsonSchema.min) {
    yupSchema = withMin(yupSchema, jsonSchema);
  }

  if (jsonSchema.max) {
    yupSchema = withMax(yupSchema, jsonSchema);
  }

  if (jsonSchema.minYear) {
    yupSchema = withMinYear(yupSchema, jsonSchema);
  }

  if (jsonSchema.maxYear) {
    yupSchema = withMaxYear(yupSchema, jsonSchema);
  }

  if (jsonSchema.minAgeMonths !== undefined) {
    yupSchema = withMinAgeMonths(yupSchema, jsonSchema);
  }

  if (jsonSchema.maxAgeMonths !== undefined) {
    yupSchema = withMaxAgeMonths(yupSchema, jsonSchema);
  }

  if (jsonSchema.required === true || forceRequired === true) {
    yupSchema = withRequired(yupSchema, jsonSchema);
  }

  if (jsonSchema?.errors?.typeError !== undefined) {
    yupSchema = withTypeError(yupSchema as any, jsonSchema);
  }

  if (jsonSchema.nullable !== undefined) {
    yupSchema = withNullable(yupSchema, jsonSchema);
  }

  if (jsonSchema.strict) {
    yupSchema = withStrict(yupSchema, jsonSchema);
  }

  if (jsonSchema.when) {
    yupSchema = withWhen(yupSchema, jsonSchema.when);
  }

  return yupSchema;
};

function withMinDate(
  schema: DateSchema,
  dateValue: Date,
  errorMessage?: string
): DateSchema {
  return schema.min(dateValue, errorMessage);
}

function withMin(schema: DateSchema, jsonSchema: DateTypeSchema): DateSchema {
  const date = valueToDate(jsonSchema.min);

  if (date != null) {
    return withMinDate(schema, date, jsonSchema?.errors?.min);
  }

  schema = schema['min'](
    refValue(jsonSchema['min']),
    get(jsonSchema.errors, 'min')
  );

  return schema;
}

function withMinYear(
  schema: DateSchema,
  jsonSchema: DateTypeSchema
): DateSchema {
  const date = startOfYear(new Date(jsonSchema.minYear));

  if (date != null) {
    return withMinDate(schema, date, jsonSchema?.errors?.minYear);
  }

  return schema;
}

function withMaxYear(
  schema: DateSchema,
  jsonSchema: DateTypeSchema
): DateSchema {
  const date = endOfYear(new Date(jsonSchema.maxYear));

  if (date != null) {
    return withMaxDate(schema, date, jsonSchema?.errors?.maxYear);
  }

  return schema;
}

function withMaxDate(
  schema: DateSchema,
  dateValue: Date,
  errorMessage?: string
): DateSchema {
  return schema.max(dateValue, errorMessage);
}

function withMax(schema: DateSchema, jsonSchema: DateTypeSchema): DateSchema {
  const date = valueToDate(jsonSchema.max);

  if (date != null) {
    return withMaxDate(schema, date, jsonSchema?.errors?.max);
  }

  schema = schema['max'](
    refValue(jsonSchema['max']),
    get(jsonSchema.errors, 'max')
  );

  return schema;
}

function withMinAgeMonths(
  schema: DateSchema,
  jsonSchema: DateTypeSchema
): DateSchema {
  if (typeof jsonSchema.minAgeMonths === 'number') {
    const date = subMonths(new Date(), Math.round(jsonSchema.minAgeMonths));
    // end of day so any date on that day is valid in the yup max check
    date.setHours(23);
    date.setMinutes(59);
    date.setSeconds(59);
    date.setMilliseconds(0);

    return withMaxDate(schema, date, jsonSchema?.errors?.minAgeMonths);
  }

  return schema;
}

function withMaxAgeMonths(
  schema: DateSchema,
  jsonSchema: DateTypeSchema
): DateSchema {
  if (typeof jsonSchema.maxAgeMonths === 'number') {
    const date = subMonths(new Date(), Math.round(jsonSchema.maxAgeMonths));
    // start of day so any date on that day is valid in the yup min check
    date.setHours(0);
    date.setMinutes(0);
    date.setSeconds(0);
    date.setMilliseconds(0);

    return withMinDate(schema, date, jsonSchema?.errors?.maxAgeMonths);
  }

  return schema;
}

function withRequired(
  schema: DateSchema,
  jsonSchema: DateTypeSchema
): DateSchema {
  return schema.required(jsonSchema?.errors?.required);
}

function withNullable(
  schema: DateSchema,
  jsonSchema: DateTypeSchema
): DateSchema {
  return schema.nullable(jsonSchema.nullable as any);
}

function withStrict(
  schema: DateSchema,
  jsonSchema: DateTypeSchema
): DateSchema {
  return schema.strict(jsonSchema.strict);
}

export default toYupDate;
