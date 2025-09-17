import { toYup } from '../../../toYup';
import { YupTypeSchema } from '../../../types';

const schema = {
  type: 'object',
  properties: {
    blog: {
      type: 'object',
      properties: {
        minimum_name_length: {
          type: 'number',
          errors: {
            required: '[minimum_name_length_description_required]'
          },
          min: 1,
          label: 'Minimum name length'
        },
        maximum_name_length: {
          type: 'number',
          errors: {
            required: '[maximum_name_length_description_required]'
          },
          sign: 'positive',
          integer: true,
          min: { ref: 'minimum_name_length' },
          label: 'Maximum name length'
        }
      }
    }
  }
};

const yupSchema = toYup(schema as YupTypeSchema);

describe('yup.ref nested', () => {
  test('min_length expect fail', async () => {
    expect(
      yupSchema.isValidSync({
        blog: { minimum_name_length: 12, maximum_name_length: 1 }
      })
    ).toBe(false);
    expect(
      yupSchema.isValidSync({
        blog: { minimum_name_length: 12, maximum_name_length: 111 }
      })
    ).toBe(true);
  });
});
