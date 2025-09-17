import { get, uniq } from 'lodash';
import * as yup from 'yup';
import { ArraySchema, SchemaOf as Schema } from 'yup';
import { toYup, withTypeError } from '../../toYup';
import { ArrayTypeSchema, BuildCustomSchema } from '../../types';
import withWhen from '../withWhen';
import { when } from '@metafox/utils';

yup.addMethod(yup.array, 'maxWhen', function isValid(data, message) {
  return this.test('maxWhen', message, list => {
    const { value: max, when: whenCondition } = data;

    if (!list) return true;

    const filterList = list.filter(x => when({ item: x }, whenCondition));

    if (filterList.length > max) return false;

    return true;
  });
});

yup.addMethod(yup.array, 'minWhen', function isValid(data, message) {
  return this.test('minWhen', message, list => {
    const { value: min, when: whenCondition } = data;

    if (!list) return true;

    const filterList = list.filter(x => when({ item: x }, whenCondition));

    if (filterList.length < min) return false;

    return true;
  });
});

yup.addMethod(yup.array, 'unique', function isValid(property, message) {
  if (!property) {
    return this.test('unique', () => {
      return true;
    });
  }

  return this.test('unique', message, list => {
    const filtered = list.filter(Boolean);

    if (filtered.length !== uniq(filtered).length) {
      return false;
    }
  });
});

yup.addMethod(yup.array, 'uniqueBy', function isValid(property, message) {
  const mapper = obj => get(obj, property);

  // test only not empty item
  return this.test('uniqueBy', message, (list, context) => {
    const filtered = list.map(mapper).filter(Boolean);

    // return with path of object
    if (filtered.length !== uniq(filtered).length) {
      return context.createError({ message });
    }

    return true;
  });
});

type ArrayOf = ArraySchema<any>;

const toYupArray = <T>(
  jsonSchema: ArrayTypeSchema,
  forceRequired?: boolean,
  builder?: BuildCustomSchema
): ArrayOf => {
  let yupSchema = yup.array<T>() as any;

  if (jsonSchema.of != null) {
    yupSchema = withOf(yupSchema, jsonSchema, forceRequired, builder);
  }

  if (jsonSchema.label) {
    yupSchema = yupSchema.label(jsonSchema.label);
  }

  if (jsonSchema.required === true || forceRequired === true) {
    yupSchema = withRequired(yupSchema, jsonSchema);
  }

  yupSchema = withWhen(yupSchema, jsonSchema.when);

  if (jsonSchema.nullable != null) {
    yupSchema = withNullable(yupSchema, jsonSchema);
  }

  if (jsonSchema?.errors?.typeError != null) {
    yupSchema = withTypeError(yupSchema as any, jsonSchema);
  }

  if (jsonSchema.strict) {
    yupSchema = yupSchema.strict(jsonSchema.strict);
  }

  const errors = jsonSchema.errors;

  ['min', 'max'].forEach(constraint => {
    if (jsonSchema[constraint] != null) {
      yupSchema = yupSchema[constraint](
        jsonSchema[constraint],
        get(errors, constraint)
      );
    }
  });

  ['unique', 'uniqueBy'].forEach(constraint => {
    if (jsonSchema[constraint]) {
      yupSchema = yupSchema[constraint](
        jsonSchema[constraint],
        get(errors, constraint)
      );
    }
  });

  ['minWhen', 'maxWhen'].forEach(constraint => {
    if (jsonSchema[constraint] != null) {
      yupSchema = withMinMaxWhen(yupSchema, jsonSchema, constraint);
    }
  });

  return yupSchema;
};

function withMinMaxWhen(
  schema: ArrayOf,
  jsonSchema: ArrayTypeSchema,
  constraint: string
): ArrayOf {
  return schema[constraint](
    jsonSchema[constraint],
    jsonSchema?.errors ? jsonSchema.errors[constraint] : undefined
  );
}

function withOf(
  schema: ArrayOf,
  jsonSchema: ArrayTypeSchema,
  forceRequired: boolean,
  builder: BuildCustomSchema
): ArrayOf {
  return schema.of(toYup(jsonSchema.of, forceRequired, builder) as Schema<any>);
}

function withRequired(schema: ArrayOf, jsonSchema: ArrayTypeSchema): ArrayOf {
  return schema.required(jsonSchema?.errors?.required);
}

function withNullable(schema: ArrayOf, jsonSchema: ArrayTypeSchema): ArrayOf {
  return schema.nullable(jsonSchema.nullable as any);
}

export default toYupArray;
