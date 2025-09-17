import { toYup } from '../../../toYup';
import { YupTypeSchema } from '../../../types';

const schema = {
  type: 'object',
  properties: {
    min_length: {
      type: 'number',
      min: 1,
      max: 255
    },
    max_length: {
      type: 'number',
      min: { ref: 'min_length' }
    }
  }
};

const yupSchema = toYup(schema as YupTypeSchema);

describe('yup.ref', () => {
  test('min_length expect fail', async () => {
    expect(yupSchema.isValidSync({ min_length: -1 })).toBe(false);
    expect(yupSchema.isValidSync({ min_length: 0 })).toBe(false);
    expect(yupSchema.isValidSync({ min_length: 256 })).toBe(false);
  });

  test('ref expect fail', async () => {
    expect(yupSchema.isValidSync({ min_length: 10, max_length: 9 })).toBe(
      false
    );
    expect(yupSchema.isValidSync({ min_length: 10, max_length: -10 })).toBe(
      false
    );
  });

  test('ref expect true', async () => {
    expect(yupSchema.isValidSync({ min_length: 10 })).toBe(true);
    expect(yupSchema.isValidSync({ min_length: 10, maxLength: 10 })).toBe(true);
    expect(yupSchema.isValidSync({ min_length: 10, maxLength: 11 })).toBe(true);
  });
});
