var onloadCallback = function() {
  grecaptcha.render('recaptcha', {
  'sitekey' : '6Leg0-MqAAAAANAOiv6HCtXQgA5A29n827B68cxw' 
  });
};

function validateRecaptcha() {
  var response = grecaptcha.getResponse();
  var errorDiv = document.getElementById("recaptcha-error");

  if (!response) {
      errorDiv.innerHTML = "⚠ Please complete the reCAPTCHA.";
      errorDiv.style.display = "block";
      return false;
  }

  errorDiv.style.display = "none"; // Hide error if CAPTCHA is completed
  return true;
}

// Reset error message when reCAPTCHA is clicked
function recaptchaCallback() {
  document.getElementById("recaptcha-error").style.display = "none";
}

function onClick(e) {
  e.preventDefault();
  grecaptcha.ready(function() {
    grecaptcha.execute('reCAPTCHA_site_key', {action: 'submit'}).then(function(token) {
        // Add your logic to submit to your backend server here.
    });
  });
}