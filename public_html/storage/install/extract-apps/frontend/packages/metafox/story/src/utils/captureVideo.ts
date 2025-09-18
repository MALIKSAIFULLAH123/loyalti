const snapshots = [];

function capture(video) {
  const canvas = document.createElement('canvas');
  canvas.width = video.clientWidth;
  canvas.height = video.clientHeight;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(video, 0, 0, video.clientWidth, video.clientHeight);

  return canvas;
}

export function captureVideo(video) {
  try {
    const canvas = capture(video);
    const elementVideo = document.getElementById('captureVideoId');
    const elementThumb = document.getElementById('captureThumbId');

    if (!elementVideo) return;

    canvas.onclick = () => {
      window.open(canvas.toDataURL('image/jpg'));
    };
    snapshots.unshift(canvas);
    elementVideo.innerHTML = '';
    elementVideo.appendChild(snapshots[0]);

    if (elementThumb) {
      elementThumb.innerHTML = '';
      elementThumb.appendChild(snapshots[0]);
    }

    document.getElementById('videoMobileId')?.remove();
    document.getElementById('videoThumbMobileId')?.remove();
  } catch (err) {}
}
