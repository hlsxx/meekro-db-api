/** vygeneruj-uid */
var formdata = new FormData();
var requestOptions = {
  method: 'GET',
  body: formdata,
  redirect: 'follow'
};

var testingUID = '';
fetch("localhost/holes/ucm/meekro-api/index.php?page=vygeneruj-uid", requestOptions)
  .then(response => response.text())
  .then(result => testingUID = result.unknownUserUID)
  .catch(error => console.log('error', error));
// {"status":"success","unknownUserUID":"637628667ee28"}