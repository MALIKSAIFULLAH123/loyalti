import to from 'await-to-js';
import { ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { ObjectTypeSchema } from '../../../types';

const schema: ObjectTypeSchema = {
  type: 'object',
  strict: true,
  properties: {
    shareName: {
      type: 'boolean',
      strict: true,
      required: true
    },
    name: {
      type: 'string',
      strict: true,
      when: [
        {
          fields: 'shareName',
          is: true,
          then: {
            type: 'string',
            minLength: 1,
            errors: {
              minLength: 'Must fill name in when shareName is true'
            }
          },
          otherwise: {
            type: 'string',
            maxLength: 0,
            errors: {
              maxLength: 'Must not fill name in when shareName is true'
            }
          }
        }
      ]
    }
  }
};

const yupSchema = toYup(schema) as any;

test('when string expect fail', async () => {
  expect(
    yupSchema.isValidSync({
      shareName: true,
      name: ''
    })
  ).toBe(false);

  expect(
    yupSchema.isValidSync({
      shareName: false,
      name: 'William'
    })
  ).toBe(false);
});

test('when string expect pass', async () => {
  expect(
    yupSchema.isValidSync({
      shareName: true,
      name: 'William'
    })
  ).toBe(true);

  expect(
    yupSchema.isValidSync({
      shareName: false,
      name: ''
    })
  ).toBe(true);
});

test('when string shareName true expect errors', async () => {
  const [error] = await to(
    yupSchema.validate({
      shareName: true,
      name: ''
    })
  );
  const yupError = error as ValidationError;
  expect(yupError.errors).toContain('Must fill name in when shareName is true');
});

test('when string shareName false expect errors', async () => {
  const [error] = await to(
    yupSchema.validate({
      shareName: false,
      name: 'William'
    })
  );
  const yupError = error as ValidationError;

  expect(yupError.errors).toContain(
    'Must not fill name in when shareName is true'
  );
});
