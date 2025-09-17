import * as React from 'react';
import { MemoryRouter } from 'react-router';
import renderer from 'react-test-renderer';
import { CategoryList } from './CategoryList';

describe('CategoryList', () => {
  it('render null', async () => {
    const props = {
      data: [],
      to: ''
    };
    const tree = renderer.create(<CategoryList {...props} />).toJSON();
    expect(tree).toMatchSnapshot();
  });
  it('render item', async () => {
    const props = {
      data: [
        { id: 1, name: 'category 1' },
        { id: 2, name: 'category 2' }
      ],
      to: '/blog'
    };
    const tree = renderer
      .create(
        <MemoryRouter>
          <CategoryList {...props} />
        </MemoryRouter>
      )
      .toJSON();
    expect(tree).toMatchSnapshot();
  });
});
