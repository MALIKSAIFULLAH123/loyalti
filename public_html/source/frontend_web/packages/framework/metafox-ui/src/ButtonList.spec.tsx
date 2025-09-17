import { render } from '@testing-library/react';
import * as React from 'react';
import ButtonList from './ButtonList';

const MockChildrenComponent = () => (
  <div data-testid="children">content children</div>
);

describe('ButtonList', () => {
  it('render ButtonList', () => {
    const { container } = render(<ButtonList />);
    expect(container).toMatchSnapshot();
  });
  it('render prop variant', () => {
    const props = {
      variant: 'fillFirst'
    };
    const { container, queryByTestId } = render(
      <ButtonList {...props}>
        <MockChildrenComponent />
      </ButtonList>
    );
    expect(queryByTestId('children')).toBeInTheDocument();
    expect(container).toMatchSnapshot();
  });
  it('render prop variant medium', () => {
    const props = {
      variant: 'fillFirst',
      spacing: 'medium'
    };
    const { container, queryByTestId } = render(
      <ButtonList {...props}>
        <MockChildrenComponent />
      </ButtonList>
    );
    expect(queryByTestId('children')).toBeInTheDocument();
    expect(queryByTestId('children')).toHaveTextContent('content children');
    expect(container).toMatchSnapshot();
  });
});
