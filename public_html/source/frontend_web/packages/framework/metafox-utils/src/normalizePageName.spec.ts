import normalizePageName from './normalizePageName';

describe('normalizePageName', () => {
  it('blog.blog_settings', () => {
    expect(normalizePageName('blog', false, false, false, 'settings')).toEqual(
      'blog.blog_settings'
    );
  });
  it('core.google_settings', () => {
    expect(normalizePageName('core', 'google', null, null, 'settings')).toEqual(
      'core.google_settings'
    );
  });
  it('blog.browse_blog', () => {
    expect(normalizePageName('blog', 'blog', 'browse')).toEqual(
      'blog.browse_blog'
    );
  });

  it('blog.browse_home_category', () => {
    expect(normalizePageName('blog', 'category', 'browse', 'home')).toEqual(
      'blog.browse_home_category'
    );
  });
});
