import to from 'await-to-js';
import { ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { ArrayTypeSchema } from '../../../types/index';

const errorMsg = 'Missing Required Value';
const schema: ArrayTypeSchema = {
  type: 'array',
  errors: {
    required: errorMsg
  }
};

const yupSchema = toYup(schema, true);

test('force required expect pass', async () => {
  expect(yupSchema.isValidSync(['abc'])).toBe(true);
});

test('force required expect fail', async () => {
  expect(yupSchema.isValidSync(undefined)).toBe(false);
});

test('force required expect error', async () => {
  const [error] = await to(yupSchema.validate(undefined));
  expect((error as ValidationError).message).toBe(errorMsg);
});
