import to from 'await-to-js';
import { NumberSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { NumberTypeSchema } from '../../../types';

const errorMsg = 'Must be 1 or 2';

const schema: NumberTypeSchema = {
  type: 'number',
  strict: true,
  oneOf: [1, 2],
  errors: {
    oneOf: errorMsg
  }
};

const yupSchema = toYup(schema) as NumberSchema;

test('oneOf expect fail', async () => {
  expect(yupSchema.isValidSync(-5.1)).toBe(false);
  expect(yupSchema.isValidSync([0])).toBe(false);
  expect(yupSchema.isValidSync([5, 2])).toBe(false);
  expect(yupSchema.isValidSync(0.2)).toBe(false);
});

test('oneOf expect pass', async () => {
  expect(yupSchema.isValidSync(1)).toBe(true);
  expect(yupSchema.isValidSync(2)).toBe(true);
});

test('oneOf expect fail message', async () => {
  const [error] = await to(yupSchema.validate(0));
  expect((error as ValidationError).message).toBe(errorMsg);
});
