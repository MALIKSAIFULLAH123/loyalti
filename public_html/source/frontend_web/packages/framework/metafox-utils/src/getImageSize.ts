type Size = { width: number; height: number };
type Queue = () => void;
const state: {
  queue: Queue[];
  running: boolean;
} = {
  running: false,
  queue: []
};

function next() {
  if (state.queue.length && !state.running) {
    const fn = state.queue.shift();

    if (fn) fn();
  }
}

async function fetchSize(src: string) {
  // process on queue to reduce network bottle-neck

  return new Promise<Size>((resolve, reject) => {
    const img = new Image();
    state.running = true;

    img.onload = (e: any) => {
      resolve({
        width: e.target.naturalWidth,
        height: e.target.naturalHeight
      });
      state.running = false;
      next();
    };

    img.onerror = (e: any) => {
      reject(e);
      state.running = false;
      next();
    };

    img.src = src;
  });
}

async function waitUntil(src: string) {
  return new Promise<string>(resolve => {
    const queue = () => resolve(src);
    state.queue.push(queue);
  })
    .then(src => fetchSize(src))
    .catch(err => {
      throw new Error(err);
    });
}

export default async function getImageSize(src: string): Promise<Size> {
  const promise = waitUntil(src);
  next();

  return promise;
}
