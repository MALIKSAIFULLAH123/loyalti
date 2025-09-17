import to from 'await-to-js';
import { StringSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { StringTypeSchema } from '../../../types';

const errorMsg = 'Missing Required Value';
const schema: StringTypeSchema = {
  type: 'string',
  errors: {
    required: errorMsg
  }
};

const yupSchema = toYup(schema, true) as StringSchema;

test('force required expect pass', async () => {
  expect(yupSchema.isValidSync('a')).toBe(true);
});

test('force required expect fail', async () => {
  expect(yupSchema.isValidSync('')).toBe(false);
});

test('force required expect error', async () => {
  const [error] = await to(yupSchema.validate(''));
  expect((error as ValidationError).message).toBe(errorMsg);
});
