import to from 'await-to-js';
import { ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { ObjectTypeSchema } from '../../../types';

const errorMsg = 'Missing Required Value';
const schema: ObjectTypeSchema = {
  type: 'object',
  properties: {
    name: {
      type: 'string',
      errors: {
        required: errorMsg
      }
    }
  }
};

const yupSchema = toYup(schema, true) as any;

test('force required expect pass', async () => {
  expect(
    yupSchema.isValidSync({
      name: 'hello'
    })
  ).toBe(true);
});

test('force required expect fail', async () => {
  expect(
    yupSchema.isValidSync({
      blah: 'hello'
    })
  ).toBe(false);
});

test('force required expect error', async () => {
  const [error] = await to(yupSchema.validate(undefined));
  expect((error as ValidationError).message).toBe(errorMsg);
});
