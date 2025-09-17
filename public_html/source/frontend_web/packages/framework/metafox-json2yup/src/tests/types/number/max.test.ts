import to from 'await-to-js';
import { NumberSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { NumberTypeSchema } from '../../../types';

const errorMsg = 'Max 5';

const schema: NumberTypeSchema = {
  type: 'number',
  strict: true,
  max: 5,
  errors: {
    max: errorMsg
  }
};

const yupSchema = toYup(schema) as NumberSchema;

test('max expect fail', async () => {
  expect(yupSchema.isValidSync(6)).toBe(false);
  expect(yupSchema.isValidSync(100)).toBe(false);
  expect(yupSchema.isValidSync(20)).toBe(false);
  expect(yupSchema.isValidSync(Number.MAX_VALUE)).toBe(false);
  expect(yupSchema.isValidSync(Number.MAX_SAFE_INTEGER)).toBe(false);
});

test('max expect pass', async () => {
  expect(yupSchema.isValidSync(5)).toBe(true);
  expect(yupSchema.isValidSync(1)).toBe(true);
  expect(yupSchema.isValidSync(0)).toBe(true);
  expect(yupSchema.isValidSync(-1)).toBe(true);
  expect(yupSchema.isValidSync(Number.MIN_VALUE)).toBe(true);
  expect(yupSchema.isValidSync(Number.MIN_SAFE_INTEGER)).toBe(true);
});

test('min expect fail message', async () => {
  const [error] = await to(yupSchema.validate(6));
  expect((error as ValidationError).message).toBe(errorMsg);
});
