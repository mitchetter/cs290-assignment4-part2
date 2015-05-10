
function verifyAdd() {
  var movTitle = document.getElementById('movTitle').value;
  var genre = document.getElementById('genre').value;
  var movLength = document.getElementById('movLength').value;
  var errMsg = 'Please fix the following problems: ';
  var showErrs = false;

  if (movTitle === '') {
    errMsg += 'Video name is a required field.';
    showErrs = true;
  }

  if (movLength != '' && movLength < 1) {
    if (showErrs) {
      errMsg += ' Length must be a positive number.';
    }
    else{
      errMsg += 'Length must be a positive number.';
    }
    showErrs = true;
  }

  if (showErrs) {
    alert(errMsg);
    return false;
  }

  return true;
}


function deleteVid(movTitle) {
  var path = 'https://web.engr.oregonstate.edu/~etterm/cs290-assignment4part2/movieLib.php';
  var params = {movDelete: movTitle};
  var method = 'POST';
  sendForm(path, params, method);
}

/**
* Help found at this reference:
* http://stackoverflow.com/questions/133925/javascript-post-request-like-a-form-submit.
*/
function sendForm(path, params, method) {
  var form = document.createElement('form');
  form.setAttribute('method', method);
  form.setAttribute('action', path);

  for (var key in params) {
      if (params.hasOwnProperty(key)) {
          var hiddenField = document.createElement('input');
          hiddenField.setAttribute('type', 'hidden');
          hiddenField.setAttribute('name', key);
          hiddenField.setAttribute('value', params[key]);

          form.appendChild(hiddenField);
       }
  }

  document.body.appendChild(form);
  form.submit();
}

function updateCheckout(action, movTitle) {
  var path = 'https://web.engr.oregonstate.edu/~etterm/cs290-assignment4part2/movieLib.php';
  var params;

  if (action === 'Check out') {
    params = {checkout: true, name: movTitle};
  }
  else if (action === 'Check in') {
    params = {checkout: false, name: movTitle};
  }
  else {
    return;
  }

  sendForm(path, params, 'POST');
}

function deleteLibrary() {
  var path = 'https://web.engr.oregonstate.edu/~etterm/cs290-assignment4part2/movieLib.php';
  var params = {delAll: true};

  sendForm(path, params, 'POST');
}


function categorySelect() {
  var selObject = document.getElementById('category');
  var path = 'https://web.engr.oregonstate.edu/~etterm/cs290-assignment4part2/movieLib.php';
  var params = {filter: selObject.value};

  sendForm(path, params, 'POST');
}
 