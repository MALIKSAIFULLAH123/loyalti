const toggleCaptchaBadge = (show: boolean) => {
  const badge = document.getElementsByClassName('grecaptcha-badge')[0];
  const isActive = document.getElementsByClassName('recaptcha-field')[0];

  if (badge && badge instanceof HTMLElement) {
    badge.style.visibility = show || isActive ? 'visible' : 'hidden';
  }
};

export default toggleCaptchaBadge;
