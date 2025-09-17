import { Manager } from '@metafox/framework';
import { render } from '@testing-library/react';
import React from 'react';
import { isValidElementType } from 'react-is';
import JsxBackend from './index';

const SimpleGrid = () => (
  <div data-testid="block.simple.grid">grid content</div>
);

const SimpleList = () => (
  <div data-testid="block.simple.list">list content</div>
);

const SimpleItem = () => (
  <div data-testid="block.simple.item">item content</div>
);

describe('JsxBackend default', () => {
  const jsxBackend = new JsxBackend({
    'block.simple.grid': SimpleGrid
  });

  jsxBackend.use({
    'block.simple.list': SimpleList,
    'block.simple.item': SimpleItem
  });

  it('isValidElementType', () => {
    expect(jsxBackend.get('block.simple.list')).toEqual(SimpleList);
    expect(jsxBackend.get('block.simple._non')).toBeFalsy();

    expect(isValidElementType('block.simple.grid')).toBeTruthy();
    expect(isValidElementType('block.simple.list')).toBeTruthy();
    expect(isValidElementType('block.simple.item')).toBeTruthy();
  });

  it('has()', () => {
    expect(jsxBackend.has('block.simple.grid')).toBeTruthy();
    expect(jsxBackend.has('block.simple.list')).toBeTruthy();
    expect(jsxBackend.has('block.simple.item')).toBeTruthy();
    expect(jsxBackend.has('block.simple.grid_noop')).toBeFalsy();
    expect(jsxBackend.has('block.simple.list_noop')).toBeFalsy();
    expect(jsxBackend.has('block.simple.item_noop')).toBeFalsy();
  });
  it('render()', () => {
    const { getByTestId } = render(
      <div>{jsxBackend.render({ component: 'block.simple.grid' })}</div>
    );

    expect(getByTestId('block.simple.grid')).toBeInTheDocument();
  });
  it('renderAll()', () => {
    const { getByTestId } = render(
      <div>
        {jsxBackend.render([
          { component: 'block.simple.grid', props: { key: 'c1' } },
          { component: 'block.simple.list', props: { key: 'c2' } },
          { component: 'block.simple.item', props: { key: 'c3' } }
        ])}
      </div>
    );

    expect(getByTestId('block.simple.grid')).toBeInTheDocument();
    expect(getByTestId('block.simple.list')).toBeInTheDocument();
    expect(getByTestId('block.simple.item')).toBeInTheDocument();
  });

  it('jsxBackend.render with empty array', () => {
    expect(jsxBackend.render([]).length).toEqual(0);
  });

  it('jsxBackend.render invalid element type', () => {
    const $c = undefined;
    expect(isValidElementType($c)).toEqual(false);
    expect(jsxBackend.render({ component: $c })).toEqual(null);
  });

  it('jsxBackend.render with invalid arguments array', () => {
    expect(jsxBackend.render([false, null, 'undefined'] as any)).toEqual([]);
  });
});

describe('JsxBackend + Manager', () => {
  const manager = Manager.factory({}).use({ jsxBackend: JsxBackend });
  const backend = manager.jsxBackend;

  it('+bootstrap', () => {
    expect(backend).toBeDefined();
  });

  it('+getAll()', () => {
    expect(backend.getAll()).toMatchObject({});
  });
});

describe('JsxBackend extend', () => {
  const jsxBackend = new JsxBackend({
    'blog.itemView.mainCard': () => <div />,
    'blog.itemView.smallCard': () => <div />,
    'blog.itemView.largeCard': () => <div />,
    'blog.itemView.plainText': () => <div />,
    'friend_request.itemView.plainText': () => <div />,
    'friend_request.itemView.card': () => <div />,
    'friend_request.itemView.simpleCard': () => <div />,
    'feed.itemView.mainCard': () => <div />,
    'feed.itemView.simpleCard': () => <div />,
    'feed.none.none': () => <div />
  });

  it('getVariantsByPrefix', () => {
    expect(jsxBackend.find(x => x.startsWith('blog.itemView')).length).toEqual(
      4
    );

    expect(
      jsxBackend.find(x => x.startsWith('friend_request.itemView')).length
    ).toEqual(3);

    expect(jsxBackend.find(x => x.startsWith('feed.itemView')).length).toEqual(
      2
    );
    expect(jsxBackend.find(x => x.startsWith('none.itemView')).length).toEqual(
      0
    );
    expect(jsxBackend.find(x => /\.itemView\./.test(x)).length).toEqual(9);
  });
});
