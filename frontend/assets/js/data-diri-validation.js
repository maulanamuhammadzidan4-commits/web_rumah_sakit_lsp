document.addEventListener('submit', function(e){
  if(e.target && e.target.id === 'usData'){
    const form = e.target;
    const errorBox = form.querySelector('#error-box');
    let errors = [];

    const phoneInput = form.querySelector('[name="phone"]');
    const phone = phoneInput ? phoneInput.value.trim() : '';

    const phoneRegEx = /^(\+62|62|0)8[1-9][0-9]{6,10}$/;

    if (phone == ''){
      errors.push('Nomor telepon harus diisi!');
    } else if (!phoneRegEx.test(phone)){
      errors.push('Nomor telepon tidak valid! (10-15 digit angka)');
    }

    if (errors.length > 0){
      e.preventDefault();
      errorBox.innerHTML = errors.map(error => `<p>${error}</p>`).join('');
      errorBox.style.display = 'block';
    } else {
      if (errorBox){
        errorBox.style.display = 'none';
      }
    }
  }
});