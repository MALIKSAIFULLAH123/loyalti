export default function openVoIpCallPopup(
  siteUrl: string,
  callId: any,
  state: any,
  width?: any,
  height?: any
) {
  // check when open window call then call method get callInfo
  localStorage.setItem('chatplus/callId', `${callId}/openCall`);

  const popupWidth = width || (window.innerWidth * 80) / 100;
  const popupHeight = height || (window.innerHeight * 80) / 100;
  // const url = `${siteUrl}/chatplus/call/` + `${callId}?d=${state}`;
  const url = '/chatplus/call/' + `${callId}?d=${state}`;
  const screenX =
    typeof window.screenX !== 'undefined' ? window.screenX : window.screenLeft;
  const screenY =
    typeof window.screenY !== 'undefined' ? window.screenY : window.screenTop;
  const outerWidth =
    typeof window.outerWidth !== 'undefined'
      ? window.outerWidth
      : document.body.clientWidth;
  const outerHeight =
    typeof window.outerHeight !== 'undefined'
      ? window.outerHeight
      : document.body.clientHeight - 22;

  // Use `outerWidth - width` and `outerHeight - height` for help in
  // positioning the popup centered relative to the current window
  const left = screenX + (outerWidth - popupWidth) / 2;
  const top = screenY + (outerHeight - popupHeight) / 2;
  const features = `width=${popupWidth},height=${popupHeight},left=${left},top=${top},scrollbars=yes,location=no`;

  const child = window.open(
    window.location.origin + url,
    'calling_window',
    features
  );

  if (!child) {
    return;
  }

  child.focus();

  return child;
}
