import * as React from 'react';

export default function TaggedPlace({ place, className }) {
  return (
    <a
      className={className}
      rel="noopener noreferrer"
      href={`https://www.google.com/maps?daddr=${place.lat},${place.lng}`}
      target="_blank">
      {place.address}
    </a>
  );
}