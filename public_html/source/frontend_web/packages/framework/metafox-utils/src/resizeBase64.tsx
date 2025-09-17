export interface Props {
  url?: any;
  maxWidth?: number;
  maxHeight?: number;
}

export default function resizeBase64({
  url,
  maxWidth = 2000,
  maxHeight = 2000
}: Props) {
  return new Promise((resolve, reject) => {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const image = new Image();

    image.addEventListener('load', () => {
      const iw = image.width;
      const ih = image.height;
      const scale = Math.min(maxWidth / iw, maxHeight / ih);
      const iwScaled = iw * scale;
      const ihScaled = ih * scale;
      canvas.width = iwScaled;
      canvas.height = ihScaled;
      ctx.drawImage(image, 0, 0, iwScaled, ihScaled);
      resolve(canvas.toDataURL('image/jpeg'));
    });
    image.addEventListener('error', error => reject(error));
    image.src = url;
  });
}
