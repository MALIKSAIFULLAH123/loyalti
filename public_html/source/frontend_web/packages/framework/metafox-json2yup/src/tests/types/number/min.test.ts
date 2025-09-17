import to from 'await-to-js';
import { NumberSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { NumberTypeSchema } from '../../../types';

const errorMsg = 'Min 5';

const schema: NumberTypeSchema = {
  type: 'number',
  strict: true,
  min: 5,
  errors: {
    min: errorMsg
  }
};

const yupSchema = toYup(schema) as NumberSchema;

test('min expect fail', async () => {
  expect(yupSchema.isValidSync(4)).toBe(false);
  expect(yupSchema.isValidSync(-1)).toBe(false);
  expect(yupSchema.isValidSync(0)).toBe(false);
  expect(yupSchema.isValidSync([0])).toBe(false);
  expect(yupSchema.isValidSync(Number.MIN_VALUE)).toBe(false);
  expect(yupSchema.isValidSync(Number.MIN_SAFE_INTEGER)).toBe(false);
});

test('min expect pass', async () => {
  expect(yupSchema.isValidSync(5)).toBe(true);
  expect(yupSchema.isValidSync(Number.MAX_VALUE)).toBe(true);
  expect(yupSchema.isValidSync(Number.MAX_SAFE_INTEGER)).toBe(true);
});

test('min expect fail message', async () => {
  const [error] = await to(yupSchema.validate(4));
  expect((error as ValidationError).message).toBe(errorMsg);
});
