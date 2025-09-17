export const compareSameLocation = (x, y, fixed = 6) => {
  if (!x || !y) return false;

  const roundedLatX = parseFloat(x?.lat?.toFixed(fixed));
  const roundedLngX = parseFloat(x?.lng?.toFixed(fixed));
  const roundedLatY = parseFloat(y?.lat?.toFixed(fixed));
  const roundedLngY = parseFloat(y?.lng?.toFixed(fixed));

  return roundedLatX === roundedLatY && roundedLngX === roundedLngY;
};
