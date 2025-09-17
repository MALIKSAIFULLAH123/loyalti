import mergeObjectProps from './mergeObjectProps';

describe('mergeObjectProps', () => {
  it('without overrides', () => {
    const skips = {
      dataSource: {
        apiUrl: '/feed'
      }
    };

    const defaults = {
      dataSource: { apiUrl: '/blog', apiParams: { view: 'latest' } },
      blockProps: {
        marginBottom: 2
      },
      gridItemProps: {
        xs: 12,
        sm: 6,
        md: 4,
        lg: 3
      }
    };

    const values = {
      title: 'untitled',
      gridItemProps: {
        xs: 6,
        sm: 2
      }
    };

    const obj = mergeObjectProps({}, {}, defaults, values, skips);

    expect(obj).toBeDefined();
    expect(obj.dataSource.apiUrl).toBeUndefined();
    expect(obj.dataSource.apiParams.view).toEqual('latest');
    expect(obj.gridItemProps.xs).toEqual(6);
    expect(obj.gridItemProps.sm).toEqual(2);
    expect(obj.gridItemProps.md).toEqual(4);
    expect(obj.gridItemProps.lg).toEqual(3);
    expect(obj.title).toEqual('untitled');
  });

  it('with overrides', () => {
    const skips = {
      dataSource: {
        apiUrl: '/feed'
      }
    };

    const defaults = {
      dataSource: { apiUrl: '/blog', apiParams: { view: 'latest' } },
      blockProps: {
        marginBottom: 2
      },
      gridItemProps: {
        xs: 12,
        sm: 6,
        md: 4,
        lg: 3
      }
    };

    const values = {
      title: 'untitled',
      gridItemProps: {
        xs: 6,
        sm: 2,
        do: undefined
      }
    };

    const overrides = {
      gridItemProps: { xs: 1, sm: 1 }
    };

    const obj = mergeObjectProps({}, {}, defaults, values, skips, overrides);

    expect(obj).toBeDefined();
    expect(obj.dataSource.apiUrl).toBeUndefined();
    expect(obj.dataSource.apiParams.view).toEqual('latest');
    expect(obj.gridItemProps.xs).toEqual(1);
    expect(obj.gridItemProps.sm).toEqual(1);
    expect(obj.gridItemProps.md).toEqual(4);
    expect(obj.gridItemProps.lg).toEqual(3);
    expect(obj.title).toEqual('untitled');
  });
  it('without defaults, skips', () => {
    const skips = null;
    const defaults = null;
    const values = {
      title: 'untitled',
      gridItemProps: {
        xs: 6,
        sm: 2
      }
    };

    const overrides = {
      gridItemProps: { xs: 1, sm: 1 }
    };

    const obj = mergeObjectProps({}, {}, defaults, values, skips, overrides);

    expect(obj).toBeDefined();
    expect(obj.gridItemProps.xs).toEqual(1);
    expect(obj.gridItemProps.sm).toEqual(1);
    expect(obj.title).toEqual('untitled');
  });

  it('without defaults, values, skips', () => {
    const skips = null;
    const defaults = null;
    const values = null;

    const overrides = {
      gridItemProps: { xs: 1, sm: 1 }
    };

    const obj = mergeObjectProps({}, {}, defaults, values, skips, overrides);

    expect(obj).toBeDefined();
    expect(obj.gridItemProps.xs).toEqual(1);
    expect(obj.gridItemProps.sm).toEqual(1);
  });

  it('merge with array', () => {
    const values = {
      contents: [
        {
          name: 'core.block.searchBox'
        },
        {
          name: 'core.block.sidebarAppMenu'
        },
        {
          name: 'core.categoryBlock'
        }
      ]
    };
    const defaults = {
      blockProps: {
        testid: 'sidebarMenu',
        title: 'App Menu Mobile'
      },
      contents: [
        {
          name: 'core.block.searchBox'
        },
        {
          name: 'core.block.sidebarAppMenu'
        },
        {
          name: 'core.dividerBlock'
        },
        {
          name: 'core.categoryBlock'
        }
      ]
    };
    const obj = mergeObjectProps(
      {},
      {},
      defaults,
      values,
      undefined,
      undefined
    );
    expect(obj.contents.length).toEqual(3);
  });
});
