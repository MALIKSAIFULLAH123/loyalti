import to from 'await-to-js';
import { StringSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { StringTypeSchema } from '../../../types';

const errorMsg = 'Must be "hi" or "bye"';
const schema: StringTypeSchema = {
  type: 'string',
  strict: true,
  matches: { regex: '(hi|bye)' },
  errors: {
    matches: errorMsg
  }
};

const yupSchema = toYup(schema) as StringSchema;

test('matches expect fail', async () => {
  expect(yupSchema.isValidSync('hello')).toBe(false);
});

test('matches expect pass', async () => {
  expect(yupSchema.isValidSync('hi')).toBe(true);
  expect(yupSchema.isValidSync('bye')).toBe(true);
});

test('matches expect error message', async () => {
  const [error] = await to(yupSchema.validate('hello'));
  expect((error as ValidationError).message).toBe(errorMsg);
});

test('matches expect fail empty string', async () => {
  expect(yupSchema.isValidSync('')).toBe(false);
});

test('matches expect ignore empty string', async () => {
  const excludeEmptyStrings: StringTypeSchema = {
    type: 'string',
    strict: true,
    matches: { regex: '(hi|bye)', excludeEmptyString: true },
    errors: {
      matches: errorMsg
    }
  };
  expect(toYup(excludeEmptyStrings).isValidSync('')).toBe(true);
});
