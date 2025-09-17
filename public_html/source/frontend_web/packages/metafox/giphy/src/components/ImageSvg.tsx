import { useGlobal } from '@metafox/framework';
import React from 'react';

const ImageSvg = ({ outline = true }) => {
  const { theme } = useGlobal();

  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 512 512"
      width="16"
      height="16"
    >
      {outline ? (
        <rect
          x="40"
          y="40"
          width="432"
          height="432"
          rx="80"
          ry="80"
          fill="none"
          stroke={theme.palette.text.secondary}
          strokeWidth="30"
        />
      ) : (
        <rect
          x="40"
          y="40"
          width="432"
          height="432"
          rx="80"
          ry="80"
          fill={theme.palette.text.secondary}
        />
      )}

      <text
        x="256"
        y="280"
        fill={outline ? theme.palette.text.secondary : '#fff'}
        fontFamily="Arial, sans-serif"
        fontSize="225"
        fontWeight="700"
        textAnchor="middle"
        dominantBaseline="middle"
      >
        GIF
      </text>
    </svg>
  );
};

export default ImageSvg;
