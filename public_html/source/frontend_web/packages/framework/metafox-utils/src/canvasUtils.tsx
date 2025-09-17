type Area = {
  width: number;
  height: number;
  x: number;
  y: number;
};

function toDecimal(percent) {
  const parsed = parseFloat(percent);

  if (!Number.isNaN(parsed)) {
    return parseFloat(percent) / 100;
  } else {
    return 0;
  }
}

const createImage = (url: string) =>
  new Promise((resolve, reject) => {
    const image = new Image();
    image.addEventListener('load', () => resolve(image));
    image.addEventListener('error', error => reject(error));
    image.setAttribute('crossOrigin', 'anonymous');
    image.src = url.startsWith('http') ? `${url}?not-from-cache` : url;
  });

function getRadianAngle(degreeValue) {
  return (degreeValue * Math.PI) / 180;
}

export default async function getCroppedImg(
  imageSrc: string,
  dataCrop: Area,
  type: string,
  rotation = 0,
  flipVertical = false,
  flipHorizontal = false,
  unit = 'px'
) {
  try {
    let pixelCrop = dataCrop;
    const image = (await createImage(imageSrc)) as HTMLImageElement;

    if (unit === '%') {
      pixelCrop = {
        x: toDecimal(dataCrop.x) * image.width,
        y: toDecimal(dataCrop.y) * image.height,
        width: toDecimal(dataCrop.width) * image.width,
        height: toDecimal(dataCrop.height) * image.height
      };
    }

    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const maxSize = Math.max(image.width, image.height);
    const safeArea = 2 * ((maxSize / 2) * Math.sqrt(2));
    const scaleX = flipVertical ? -1 : 1;
    const scaleY = flipHorizontal ? -1 : 1;
    // set each dimensions to double largest dimension to allow for a safe area for the
    // image to rotate in without being clipped by canvas context
    canvas.width = safeArea;
    canvas.height = safeArea;

    // translate canvas context to a central location on image to allow rotating around the center.
    ctx.translate(safeArea / 2, safeArea / 2);
    ctx.rotate(getRadianAngle(rotation));
    ctx.scale(scaleX, scaleY);
    ctx.translate(-safeArea / 2, -safeArea / 2);

    // draw rotated image and store data.
    ctx.drawImage(
      image,
      safeArea / 2 - image.width * 0.5,
      safeArea / 2 - image.height * 0.5
    );
    const data = ctx.getImageData(0, 0, safeArea, safeArea);

    // set canvas width to final desired crop size - this will clear existing context
    canvas.width = pixelCrop.width;
    canvas.height = pixelCrop.height;

    // paste generated rotate image with correct offsets for x,y crop values.
    ctx.putImageData(
      data,
      Math.round(0 - safeArea / 2 + image.width * 0.5 - pixelCrop.x),
      Math.round(0 - safeArea / 2 + image.height * 0.5 - pixelCrop.y)
    );

    switch (type) {
      case 'base64':
        // As Base64 string
        return canvas.toDataURL('image/jpeg');
      default:
        // As a blob
        return new Promise(resolve => {
          canvas.toBlob(blob => {
            resolve(URL.createObjectURL(blob));
          }, 'image/jpeg');
        });
    }
  } catch (err) {
    throw new Error(err);
  }
}
