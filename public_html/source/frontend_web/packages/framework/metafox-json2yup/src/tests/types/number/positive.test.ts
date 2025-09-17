import to from 'await-to-js';
import { NumberSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { NumberTypeSchema } from '../../../types';

const errorMsg = 'Must be positive';

const schema: NumberTypeSchema = {
  type: 'number',
  strict: true,
  sign: 'positive',
  errors: {
    positive: errorMsg
  }
};

const yupSchema = toYup(schema) as NumberSchema;

test('positive expect fail', async () => {
  expect(yupSchema.isValidSync(-5)).toBe(false);
  expect(yupSchema.isValidSync([0])).toBe(false);
  expect(yupSchema.isValidSync([5])).toBe(false);
  expect(yupSchema.isValidSync(0)).toBe(false);
  expect(yupSchema.isValidSync(Number.MIN_SAFE_INTEGER)).toBe(false);
});

test('positive expect pass', async () => {
  expect(yupSchema.isValidSync(4)).toBe(true);
  expect(yupSchema.isValidSync(1)).toBe(true);
  expect(yupSchema.isValidSync(0.0000001)).toBe(true);
  expect(yupSchema.isValidSync(Number.MAX_SAFE_INTEGER)).toBe(true);
});

test('positive expect fail message', async () => {
  const [error] = await to(yupSchema.validate(0));
  expect((error as ValidationError).message).toBe(errorMsg);
});
