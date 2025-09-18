export const formatTime = time => {
  // formarting duration of video
  if (isNaN(time) || time === 0) {
    return '00:00';
  }

  const date = new Date(time * 1000);
  const hours = date.getUTCHours();
  const minutes = date.getUTCMinutes();
  const seconds = date.getUTCSeconds().toString().padStart(2, '0');

  if (hours) {
    // if video have hours
    return `${hours}:${minutes.toString().padStart(2, '0')} `;
  } else return `${minutes}:${seconds}`;
};

export const getRatioPercent = (height, width) => {
  const ratio = height / width;

  if (isNaN(ratio) || ratio === 0) {
    return '56.25%';
  }

  return `${(height / width) * 100}%`;
};
