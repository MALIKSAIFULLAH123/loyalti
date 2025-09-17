import to from 'await-to-js';
import { ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { ObjectTypeSchema } from '../../../types';

const schema: ObjectTypeSchema = {
  type: 'object',
  strict: true,
  properties: {
    name: {
      type: 'string',
      strict: true,
      required: true,
      errors: {
        required: 'name required'
      }
    },
    address: {
      type: 'object',
      strict: true,
      properties: {
        streetName: {
          type: 'string',
          required: true,
          strict: true,
          errors: {
            required: 'streetName required'
          }
        },
        town: {
          type: 'string',
          required: true,
          strict: true,
          errors: {
            required: 'town required'
          }
        },
        numbers: {
          type: 'array',
          required: true,
          strict: true,
          min: 2,
          of: {
            type: 'number',
            min: 1,
            errors: {
              min: 'sub sub number array value must be more than 1'
            }
          },
          errors: {
            min: 'must have at least 2 numbers',
            required: 'numbers required'
          }
        },
        sweets: {
          type: 'object',
          strict: true,
          properties: {
            count: {
              type: 'number',
              required: true,
              strict: true,
              sign: 'positive',
              errors: {
                required: 'sweet count required',
                positive: 'sweet count must be positive'
              }
            }
          }
        }
      }
    }
  }
};

const yupSchema = toYup(schema) as any;

test('Object schema expect success', async () => {
  expect(
    yupSchema.isValidSync({
      name: 'Will',
      address: {
        streetName: 'some road',
        town: 'London',
        numbers: [2, 3],
        sweets: { count: 10 }
      }
    })
  ).toBe(true);
});

test('Object schema expect required error messages', async () => {
  const [error] = await to(
    yupSchema.validate(
      {
        name: '',
        address: {
          streetName: '',
          town: '',
          sweets: {
            count: undefined
          }
        }
      },
      { abortEarly: false }
    )
  );
  const yupError: ValidationError = error as ValidationError;
  expect(yupError.errors.includes('name required')).toBe(true);
  expect(yupError.errors.includes('streetName required')).toBe(true);
  expect(yupError.errors.includes('town required')).toBe(true);
  expect(yupError.errors.includes('sweet count required')).toBe(true);
  expect(yupError.errors.includes('numbers required')).toBe(true);

  const [error2] = await to(
    yupSchema.validate(
      {
        name: '',
        address: {
          streetName: '',
          town: '',
          numbers: [0, 1],
          sweets: {
            count: -1
          }
        }
      },
      { abortEarly: false }
    )
  );
  const yupError2: ValidationError = error2 as ValidationError;
  expect(yupError2.errors.includes('sweet count must be positive')).toBe(true);
  expect(
    yupError2.errors.includes('sub sub number array value must be more than 1')
  ).toBe(true);

  const [error3] = await to(
    yupSchema.validate(
      {
        name: '',
        address: {
          numbers: [2]
        }
      },
      { abortEarly: false }
    )
  );
  const yupError3: ValidationError = error3 as ValidationError;
  expect(yupError3.errors.includes('must have at least 2 numbers')).toBe(true);
});
