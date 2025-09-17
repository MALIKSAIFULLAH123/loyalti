import { render } from '@testing-library/react';
import * as React from 'react';
import LineIcon from './LineIcon';

describe('LineIcon', () => {
  it('render icon', async () => {
    const { container } = render(<LineIcon icon="ico-angle-right" />);

    expect(container).toMatchSnapshot();
  });
});
