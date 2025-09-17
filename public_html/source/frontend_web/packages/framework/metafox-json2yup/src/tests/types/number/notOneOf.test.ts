import to from 'await-to-js';
import { NumberSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { NumberTypeSchema } from '../../../types';

const errorMsg = "Can't be 1 or 2";

const schema: NumberTypeSchema = {
  type: 'number',
  strict: true,
  notOneOf: [1, 2],
  errors: {
    notOneOf: errorMsg
  }
};

const yupSchema = toYup(schema) as NumberSchema;

test('notOneOf expect fail', async () => {
  expect(yupSchema.isValidSync(1)).toBe(false);
  expect(yupSchema.isValidSync(2)).toBe(false);
});

test('notOneOf expect pass', async () => {
  expect(yupSchema.isValidSync(-5.1)).toBe(true);
  expect(yupSchema.isValidSync(0.2)).toBe(true);
  expect(yupSchema.isValidSync(Number.MIN_SAFE_INTEGER)).toBe(true);
  expect(yupSchema.isValidSync(Number.MAX_SAFE_INTEGER)).toBe(true);
});

test('notOneOf expect fail message', async () => {
  const [error] = await to(yupSchema.validate(1));
  expect((error as ValidationError).message).toBe(errorMsg);
});
