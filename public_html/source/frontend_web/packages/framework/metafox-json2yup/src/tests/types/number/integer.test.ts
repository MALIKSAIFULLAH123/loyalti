import to from 'await-to-js';
import { NumberSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { NumberTypeSchema } from '../../../types';

const errorMsg = 'Must be integer';

const schema: NumberTypeSchema = {
  type: 'number',
  strict: true,
  integer: true,
  errors: {
    integer: errorMsg
  }
};

const yupSchema = toYup(schema) as NumberSchema;

test('integer expect fail', async () => {
  expect(yupSchema.isValidSync(-5.1)).toBe(false);
  expect(yupSchema.isValidSync([0])).toBe(false);
  expect(yupSchema.isValidSync([5])).toBe(false);
  expect(yupSchema.isValidSync(0.2)).toBe(false);
});

test('integer expect pass', async () => {
  expect(yupSchema.isValidSync(4)).toBe(true);
  expect(yupSchema.isValidSync(1)).toBe(true);
  expect(yupSchema.isValidSync(-1)).toBe(true);
  expect(yupSchema.isValidSync(Number.MAX_SAFE_INTEGER)).toBe(true);
  expect(yupSchema.isValidSync(Number.MIN_SAFE_INTEGER)).toBe(true);
});

test('integer expect fail message', async () => {
  const [error] = await to(yupSchema.validate(1.1));
  expect((error as ValidationError).message).toBe(errorMsg);
});
