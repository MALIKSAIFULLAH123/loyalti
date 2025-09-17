import { GridProps } from '@mui/material';
import { render } from '@testing-library/react';
import * as React from 'react';
import GridItem from './GridItem';

describe('GridItem', () => {
  const props: GridProps = {
    xs: 12,
    children: <div>1</div>
  };

  it('render have children', async () => {
    const { container } = render(<GridItem {...props} />);

    expect(container).toMatchSnapshot();
  });
});
