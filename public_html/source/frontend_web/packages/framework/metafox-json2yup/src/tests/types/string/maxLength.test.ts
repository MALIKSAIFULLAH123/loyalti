import to from 'await-to-js';
import { StringSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { StringTypeSchema } from '../../../types';

const errorMsg = 'Max 5 chars';
const schema: StringTypeSchema = {
  type: 'string',
  strict: true,
  maxLength: 5,
  errors: {
    maxLength: errorMsg
  }
};
const yupSchema = toYup(schema) as StringSchema;

test('maxLength expect fail', async () => {
  expect(yupSchema.isValidSync('whats up')).toBe(false);
});

test('maxLength expect pass', async () => {
  expect(yupSchema.isValidSync('what')).toBe(true);
});

test('minLength expect fail message', async () => {
  const [error] = await to(yupSchema.validate('whats up'));
  expect((error as ValidationError).message).toBe(errorMsg);
});
