import { DateSchema } from 'yup';
import { toYup } from '../../../toYup';
import { DateTypeSchema } from '../../../types';

const schemaNullable: DateTypeSchema = {
  type: 'date',
  min: '' as any, // purposely wrong for code coverage
  max: '' as any, // purposely wrong for code coverage
  strict: true,
  nullable: true
};

const schemaNotNullable: DateTypeSchema = {
  type: 'date',
  strict: true,
  nullable: false
};

const yupNullableSchema = toYup(schemaNullable) as DateSchema;
const yupNotNullableSchema = toYup(schemaNotNullable) as DateSchema;

test('nullable expect fail', async () => {
  expect(yupNullableSchema.isValidSync([])).toBe(false);
  expect(yupNullableSchema.isValidSync({})).toBe(false);
  expect(yupNullableSchema.isValidSync('2020-01-01')).toBe(false);
});

test('nullable expect pass', async () => {
  expect(yupNullableSchema.isValidSync(new Date('2020-01-01'))).toBe(true);
  expect(yupNullableSchema.isValidSync(null)).toBe(true);
  expect(yupNullableSchema.isValidSync(undefined)).toBe(true);
});

test('not nullable expect fail', async () => {
  expect(yupNotNullableSchema.isValidSync(null)).toBe(false);
  expect(yupNotNullableSchema.isValidSync([])).toBe(false);
  expect(yupNotNullableSchema.isValidSync({})).toBe(false);
  expect(yupNotNullableSchema.isValidSync('2020-01-01')).toBe(false);
});

test('not nullable expect pass', async () => {
  expect(yupNotNullableSchema.isValidSync(new Date('2020-01-01'))).toBe(true);
});
