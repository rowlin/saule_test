
function login() {
  const params = {
    email: document.querySelector('#loginEmail').value,
    password: document.querySelector('#loginPassword').value
  }

  const http = new XMLHttpRequest()
  http.open('POST', '/login')
  http.setRequestHeader('Content-type', 'application/json')
  http.send(JSON.stringify(params)) // Make sure to stringify
  http.onload = function () {
    alert(http.responseText)
  }
}
