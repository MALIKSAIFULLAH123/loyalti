import { toYup } from '../../../toYup';
import { ArrayTypeSchema } from '../../../types';

const schemaNullable: ArrayTypeSchema = {
  type: 'array',
  strict: true,
  nullable: true
};

const schemaNotNullable: ArrayTypeSchema = {
  type: 'array',
  strict: true,
  nullable: false
};

const yupNullableSchema = toYup(schemaNullable);
const yupNotNullableSchema = toYup(schemaNotNullable);

test('nullable expect fail', async () => {
  expect(yupNullableSchema.isValidSync(123)).toBe(false);
  expect(yupNullableSchema.isValidSync(false)).toBe(false);
  expect(yupNullableSchema.isValidSync({})).toBe(false);
  expect(yupNullableSchema.isValidSync('hello')).toBe(false);
});

test('nullable expect pass', async () => {
  expect(yupNullableSchema.isValidSync([])).toBe(true);
  expect(yupNullableSchema.isValidSync(null)).toBe(true);
  expect(yupNullableSchema.isValidSync(undefined)).toBe(true);
});

test('not nullable expect fail', async () => {
  expect(yupNotNullableSchema.isValidSync(null)).toBe(false);
  expect(yupNotNullableSchema.isValidSync(true)).toBe(false);
  expect(yupNotNullableSchema.isValidSync({})).toBe(false);
  expect(yupNotNullableSchema.isValidSync('sup')).toBe(false);
  expect(yupNotNullableSchema.isValidSync(123)).toBe(false);
});

test('not nullable expect pass', async () => {
  expect(yupNotNullableSchema.isValidSync([])).toBe(true);
});
