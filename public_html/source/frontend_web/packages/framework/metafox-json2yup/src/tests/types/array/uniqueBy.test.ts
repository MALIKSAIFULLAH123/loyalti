import { toYup } from '../../../toYup';
import { ArrayTypeSchema } from '../../../types';

describe('uniqueBy', () => {
  const schemaNotNullable: ArrayTypeSchema = {
    type: 'array',
    strict: true,
    nullable: false,
    unique: false,
    uniqueBy: 'name',
    of: {
      type: 'object',
      properties: {
        name: { type: 'string' }
      }
    }
  };

  const json = toYup(schemaNotNullable);

  test('uniqueBy', async () => {
    expect(
      json.isValidSync([
        { name: 'an', id: 1 },
        { name: 'an', id: 2 }
      ])
    ).toBe(false);

    expect(
      json.isValidSync([
        { name: 'an', id: 1 },
        { name: 'an', id: 2 }
      ])
    ).toBe(false);

    expect(
      json.isValidSync([
        { name: 'an', id: 1 },
        { name: '', id: 2 },
        { name: '', id: 3 }
      ])
    ).toBe(true);

    expect(
      json.isValidSync([
        { name: 'an', id: 1 },
        { name: 'ba', id: 2 }
      ])
    ).toBe(true);
  });
});
