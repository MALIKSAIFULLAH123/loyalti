import to from 'await-to-js';
import { StringSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { StringTypeSchema } from '../../../types';

const errorMsg = 'Missing Required Value';
const schema: StringTypeSchema = {
  type: 'string',
  required: true,
  errors: {
    required: errorMsg
  }
};

const yupSchema = toYup(schema) as StringSchema;

test('required expect pass', async () => {
  expect(yupSchema.isValidSync('a')).toBe(true);
});

test('required expect fail', async () => {
  expect(yupSchema.isValidSync('')).toBe(false);
});

test('required expect error', async () => {
  const [error] = await to(yupSchema.validate(''));
  expect((error as ValidationError).message).toBe(errorMsg);
});
