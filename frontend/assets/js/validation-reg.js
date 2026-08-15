function passCheck(pw) {
  const miss = [];

  if(pw.length < 8) miss.push('Password minimal berisi 8 karakter!');
  if(!/[A-Z]/.test(pw)) miss.push('Password minimal berisi satu huruf kapital!');
  if(!/[a-z]/.test(pw)) miss.push('Password minimal berisi satu huruf biasa!');
  if(!/\d/.test(pw)) miss.push('Password minimal memiliki satu angka!');
  if(!/[@#$!%*?&]/.test(pw)) miss.push('Password minimal berisi satu huruf spesial!');

  return miss;
}

document.addEventListener('submit', function(e){
  if(e.target && e.target.id === 'usData'){
    const form = e.target;
    const errorBox = form.querySelector('#error-box');
    let errors = [];

    const usnameInput = form.querySelector('[name="usname"]');
    const pwInput = form.querySelector('[name="pw"]');
    const emailInput = form.querySelector('[name="email"]');

    const usname = usnameInput ? usnameInput.value.trim() : '';
    const pw = pwInput ? pwInput.value.trim() : '';
    const email = emailInput ? emailInput.value.trim() : '';  

    const nameRegEx = /^[a-zA-Z0-9.,\s]+$/;
    const pwCheck = passCheck(pw);
    const emailRegEx = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;


    if (usname == ''){
      errors.push('User name harus diisi!');
    } else if (usname.length < 2){
      errors.push('Nama minimal berisi 2 karakter!');
    } else if (!nameRegEx.test(usname)){
      errors.push('Nama hanya boleh berisi huruf dan spasi!');
    }

    if (pw == ''){
      errors.push('Password harus diisi!');
    } else if (pwCheck.length > 0){
      if (pwCheck.length >= 3){
        errors.push('Password harus memiliki panjang 8 karakter yang berisi minimal satu huruf besar dan kecil, angka dan simbol')
      } else if (pwCheck.length == 2){
        errors.push(`${pwCheck[0]} dan ${pwCheck[1]}`)
      } else {
        errors.push(...pwCheck);
      }
    }

    if (emailInput){
      if (email.length == 0){
        errors.push('Email harus diisi!');
      } else if (!emailRegEx.test(email)){
        errors.push('Format email tidak sesuai!');
      }
    }

    if (errors.length > 0){
      e.preventDefault();

      if (errorBox){
        errorBox.innerHTML = '<b>Error:</b><ul>' + errors.map(err => `<li>${err}</li>`).join('') + '</ul>';
        errorBox.style.display = 'block';
      }
    } else {
      if (errorBox){
        errorBox.style.display = 'none';
      }
    }
  }
});