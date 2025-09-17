import { NumberSchema } from 'yup';
import { toYup } from '../../../toYup';
import { NumberTypeSchema } from '../../../types';

const schemaNullable: NumberTypeSchema = {
  type: 'number',
  strict: true,
  nullable: true
};

const schemaNotNullable: NumberTypeSchema = {
  type: 'number',
  strict: true,
  nullable: false
};

const yupNullableSchema = toYup(schemaNullable) as NumberSchema;
const yupNotNullableSchema = toYup(schemaNotNullable) as NumberSchema;

test('nullable expect fail', async () => {
  expect(yupNullableSchema.isValidSync([])).toBe(false);
  expect(yupNullableSchema.isValidSync({})).toBe(false);
  expect(yupNullableSchema.isValidSync('123')).toBe(false);
});

test('nullable expect pass', async () => {
  expect(yupNullableSchema.isValidSync(1)).toBe(true);
  expect(yupNullableSchema.isValidSync(null)).toBe(true);
  expect(yupNullableSchema.isValidSync(undefined)).toBe(true);
});

test('not nullable expect fail', async () => {
  expect(yupNotNullableSchema.isValidSync(null)).toBe(false);
  expect(yupNotNullableSchema.isValidSync([])).toBe(false);
  expect(yupNotNullableSchema.isValidSync({})).toBe(false);
  expect(yupNotNullableSchema.isValidSync('123')).toBe(false);
});

test('not nullable expect pass', async () => {
  expect(yupNotNullableSchema.isValidSync(123)).toBe(true);
  expect(yupNotNullableSchema.isValidSync(123.2)).toBe(true);
});
