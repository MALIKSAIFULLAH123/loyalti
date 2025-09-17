import { render } from '@testing-library/react';
import * as React from 'react';
import DialogSearchInput from './DialogSearchInput';

describe('DialogSearchInput', () => {
  it('render', async () => {
    const props = {
      placeholder: 'typing key search....'
    };
    const { container } = render(<DialogSearchInput {...props} />);

    expect(container).toMatchSnapshot();
  });

  it('render onchange', async () => {
    const props = {
      placeholder: 'typing key search....',
      onChanged: () => jest.fn()
    };

    const utils = render(<DialogSearchInput {...props} />);
    const input = utils.getByTestId('searchBox');

    expect(input).toBeInTheDocument();

    expect(utils.container).toMatchSnapshot();
  });
});
