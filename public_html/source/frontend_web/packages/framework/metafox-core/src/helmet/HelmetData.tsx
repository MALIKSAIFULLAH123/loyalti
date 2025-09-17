import { useGlobal } from '@metafox/framework';
import { isObject } from 'lodash';
import * as React from 'react';
import { Helmet } from 'react-helmet-async';

export default function HelmetData() {
  const { usePageMeta } = useGlobal();
  const data = usePageMeta();
  const names = [
    'keywords',
    'description',
    'twitter:card',
    'twitter:image',
    'robots',
    'fb:app_id'
  ];
  const properties = [
    'og:site_name',
    'og:title',
    'og:description',
    'og:url',
    'og:type',
    'og:updated_time',
    'og:image',
    'og:video',
    'og:audio',
    'og:image:alt'
  ];
  const links = ['canonical'];

  const lDJson = ['schema'];

  if (!data) return null;

  return (
    <Helmet>
      <title>{data?.title}</title>
      {names.map(key => {
        return data[key] ? (
          <meta name={key} key={key} content={data[key]} />
        ) : null;
      })}
      {properties.map(key => {
        return data[key] ? (
          <meta key={key} property={key} content={data[key]} />
        ) : null;
      })}
      {links.map(key => {
        return data[key] ? <link key={key} rel={key} href={data[key]} /> : null;
      })}
      {lDJson.map(key => {
        return data[key] ? (
          <script type="application/ld+json" key={data[key]}>
            {isObject(data[key]) ? JSON.stringify(data[key]) : data[key]}
          </script>
        ) : null;
      })}
    </Helmet>
  );
}
