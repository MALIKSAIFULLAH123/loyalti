import { isArray, isPlainObject } from 'lodash';
import { collectFileItemDotProps } from './index';

describe('collectFileItemDotProps', () => {
  it('+1', () => {
    const values = {
      title: 'ddd',
      module_id: 'blog',
      owner_id: 1,
      text: '<p>&nbsp;&nbsp;&nbsp;</p>',
      categories: [],
      privacy: 0,
      published: true,
      tags: [],
      attachments: [
        {
          id: 99,
          file_name: 'pexels-photo-2041707.jpeg'
        },
        {
          id: 100,
          file_name: 'pexels-photo-2041707.jpeg'
        },
        {
          id: 101,
          file_name: 'pexels-photo-3367460.jpeg'
        },
        {
          id: 0,
          uid: 'file7',
          source:
            'blob:http://localhost:3000/3fac6bda-f0ab-4ca2-8b8b-77d00cb9d1bd',
          file: {},
          fileItemType: 'blog',
          status: 'update'
        },
        {
          id: 0,
          uid: 'file7',
          source:
            'blob:http://localhost:3000/3fac6bda-f0ab-4ca2-8b8b-77d00cb9d1bd',
          file: {},
          fileItemType: 'blog',
          status: 'update'
        }
      ],
      draft: 0,
      file: {
        id: 0,
        uid: 'file7',
        source:
          'blob:http://localhost:3000/3fac6bda-f0ab-4ca2-8b8b-77d00cb9d1bd',
        file: {},
        fileItemType: 'blog',
        status: 'update'
      }
    };

    expect(isPlainObject(values)).toBeTruthy();
    expect(isArray(values)).toBeFalsy();
    expect(Object.keys(values).length).toEqual(11);
    const dotProps = collectFileItemDotProps(values);

    expect(dotProps).toEqual(['attachments.3', 'attachments.4', 'file']);
  });
});
