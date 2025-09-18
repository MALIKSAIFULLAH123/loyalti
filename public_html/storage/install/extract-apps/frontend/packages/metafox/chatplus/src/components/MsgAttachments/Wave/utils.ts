export const getPosX = (event: TouchEvent | MouseEvent): number => {
  if (event instanceof MouseEvent) {
    return event.clientX;
  } else {
    return event.touches[0].clientX;
  }
};

type throttleFunction<T> = (arg: T) => void;

export function throttle<K>(
  func: throttleFunction<K>,
  limit: number
): throttleFunction<K> {
  let inThrottle = false;

  return arg => {
    if (!inThrottle) {
      func(arg);
      inThrottle = true;
      setTimeout(() => (inThrottle = false), limit);
    }
  };
}

export const ProgressUpdateInterval = 0;

export const formatTimeSong = time => {
  const minutes = Math.floor(time / 60);
  const seconds = Math.round(time - minutes * 60);
  const secondsFormat = `0${seconds}`.slice(-2);

  return `${minutes}:${secondsFormat}`;
};

export const parseProgressToTimer = (progress, duration) =>
  formatTimeSong(duration - (duration * progress) / 100);
