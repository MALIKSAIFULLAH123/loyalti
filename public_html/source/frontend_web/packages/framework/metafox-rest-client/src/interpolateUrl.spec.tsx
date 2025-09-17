/// <reference types="jest" />
import interpolateUrl from './interpolateUrl';

describe('interpolateUrl', () => {
  it('/user/:user_id', () => {
    const { pathname } = interpolateUrl('/user/:user_id', {
      user_id: 14
    });

    expect(pathname).toEqual('/user/14');
  });
  it('/user/:user_id', () => {
    const { pathname } = interpolateUrl('/user/:user_id', 'user_id=14');
    expect(pathname).toEqual('/user/14');
  });
  it('/user/:user_id?tab=:user_id', () => {
    const { pathname, searchParams } = interpolateUrl(
      '/user/:user_id?tab=:user_id',
      {
        user_id: 'jack',
        tab: 'friend'
      }
    );

    expect(pathname).toEqual('/user/jack');
    expect(searchParams.tab).toEqual('friend');
    expect(searchParams.user_id).toBeUndefined();
  });
  it('/user/:user_id?tab=:user_id', () => {
    const { pathname, searchParams } = interpolateUrl(
      '/user/:user_id?tab=:user_id',
      'user_id=jack&tab=friend'
    );

    expect(pathname).toEqual('/user/jack');
    expect(searchParams.tab).toEqual('friend');
    expect(searchParams.user_id).toBeUndefined();
  });
  it('/user/:user_id?tab=:tab&view=recent&limit=14', () => {
    const { pathname, searchParams } = interpolateUrl(
      '/user/:user_id?tab=:tab&view=recent&limit=14',
      {
        user_id: '9',
        tab: 'blog'
      }
    );

    expect(pathname).toEqual('/user/9');
    expect(searchParams.tab).toEqual('blog');
    expect(searchParams.user_id).toBeUndefined();
    expect(searchParams.tab).toEqual('blog');
    expect(searchParams.view).toEqual('recent');
    expect(searchParams.limit).toEqual('14');
  });
  it('/user/:user_id?tab=:tab&view=recent&limit=14', () => {
    const { pathname, searchParams } = interpolateUrl(
      '/user/:user_id?tab=:tab&view=recent&limit=14',
      'user_id=9&tab=blog'
    );

    expect(pathname).toEqual('/user/9');
    expect(searchParams.tab).toEqual('blog');
    expect(searchParams.user_id).toBeUndefined();
    expect(searchParams.tab).toEqual('blog');
    expect(searchParams.view).toEqual('recent');
    expect(searchParams.limit).toEqual('14');
  });
});
