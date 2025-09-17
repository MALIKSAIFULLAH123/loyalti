import to from 'await-to-js';
import { ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { ArrayTypeSchema } from '../../../types';

const errorMsg = 'Min 2 items';
const errorOfMsg = 'Max 5 item string length';

const schema: ArrayTypeSchema = {
  type: 'array',
  strict: true,
  required: true,
  of: {
    type: 'string',
    maxLength: 5,
    errors: {
      maxLength: errorOfMsg
    }
  },
  min: 2,
  errors: {
    min: errorMsg
  }
};

const yupSchema = toYup(schema);

test('of maxLength expect fail', async () => {
  expect(yupSchema.isValidSync(['hello', 'helloo'])).toBe(false);
  expect(yupSchema.isValidSync(['hello'])).toBe(false);
  expect(yupSchema.isValidSync([12345, 123456])).toBe(false);
});

test('of maxLength expect pass', async () => {
  expect(yupSchema.isValidSync(['hello', 'what'])).toBe(true);
  expect(yupSchema.isValidSync(['hello', 'how', 'are', 'you'])).toBe(true);
});

test('of min expect fail message', async () => {
  const [error] = await to(yupSchema.validate(['hello']));
  expect(error.message).toBe(errorMsg);
});

test('of maxLength expect fail message', async () => {
  const [error] = await to(yupSchema.validate(['hello', 'hellooo']));
  expect((error as ValidationError).message).toBe(errorOfMsg);
});
