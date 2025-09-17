import to from 'await-to-js';
import { NumberSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { NumberTypeSchema } from '../../../types';

const errorMsg = 'Less Than 5';

const schema: NumberTypeSchema = {
  type: 'number',
  strict: true,
  lessThan: 5,
  errors: {
    lessThan: errorMsg
  }
};

const yupSchema = toYup(schema) as NumberSchema;

test('lessThan expect fail', async () => {
  expect(yupSchema.isValidSync(5)).toBe(false);
  expect(yupSchema.isValidSync([0])).toBe(false);
  expect(yupSchema.isValidSync([5])).toBe(false);
  expect(yupSchema.isValidSync(Number.MAX_VALUE)).toBe(false);
  expect(yupSchema.isValidSync(Number.MAX_SAFE_INTEGER)).toBe(false);
});

test('lessThan expect pass', async () => {
  expect(yupSchema.isValidSync(4)).toBe(true);
  expect(yupSchema.isValidSync(-1)).toBe(true);
  expect(yupSchema.isValidSync(0)).toBe(true);
  expect(yupSchema.isValidSync(Number.MIN_VALUE)).toBe(true);
  expect(yupSchema.isValidSync(Number.MIN_SAFE_INTEGER)).toBe(true);
});

test('lessThan expect fail message', async () => {
  const [error] = await to(yupSchema.validate(5));
  expect((error as ValidationError).message).toBe(errorMsg);
});
