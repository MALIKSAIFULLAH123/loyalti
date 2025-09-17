import to from 'await-to-js';
import { ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { ArrayTypeSchema } from '../../../types';

const errorMinMsg = 'Min 2 items';
const errorMaxMsg = 'Max 4 items';
const errorOfMsg = 'Min 2 number array length';

const schema: ArrayTypeSchema = {
  type: 'array',
  strict: true,
  required: true,
  of: {
    type: 'array',
    min: 2,
    of: {
      type: 'number',
      strict: true
    },
    errors: {
      min: errorOfMsg
    }
  },
  min: 2,
  max: 4,
  errors: {
    min: errorMinMsg,
    max: errorMaxMsg
  }
};

const yupSchema = toYup(schema);

test('of number array expect fail', async () => {
  expect(yupSchema.isValidSync([123, 123])).toBe(false);
  expect(yupSchema.isValidSync([[123, 124]])).toBe(false);
  expect(
    yupSchema.isValidSync([
      ['123', '124'],
      ['123', '1234']
    ])
  ).toBe(false);
});

test('of number array expect pass', async () => {
  expect(
    yupSchema.isValidSync([
      [123, 124],
      [123, 124]
    ])
  ).toBe(true);
});

test('of number expect min fail message', async () => {
  const [error] = await to(yupSchema.validate([[123, 124]]));
  expect((error as ValidationError).message).toBe(errorMinMsg);
});

test('of number sub array expect min fail message', async () => {
  const [error] = await to(yupSchema.validate([[123, 124], [123]]));
  expect((error as ValidationError).message).toBe(errorOfMsg);
});

test('of number expect max fail message', async () => {
  const [error] = await to(
    yupSchema.validate([
      [123, 124],
      [123, 124],
      [123, 124],
      [123, 124],
      [123, 124]
    ])
  );
  expect((error as ValidationError).message).toBe(errorMaxMsg);
});
