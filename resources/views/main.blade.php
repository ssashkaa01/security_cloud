<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Security cloud</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js" integrity="sha512-E8QSvWZ0eCLGk4km3hxSsNmGWbLtSCSUcewDQPQWZF6pEU8GlT8a5fF32wOl1i8ftdMhssTrF/OhyGWwonTcXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        {{-- https://cryptojs.gitbook.io/docs/ --}}
        <style>
            body {
                background-image: linear-gradient(180deg, #eee, #fff 100px, #fff);
            }
        </style>
    </head>
    <body>
    <div class="container py-3">
        <header>
            <div class="d-flex flex-column flex-md-row align-items-center pb-3 mb-4 border-bottom">
                <a href="/" class="d-flex align-items-center text-dark text-decoration-none">
                    <img src="/cyber-security.svg" width="40" height="32" class="me-2"/>
                   <span class="fs-4">Security Cloud</span>
                </a>
                <nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
                    <span class="py-2 text-decoration-none text-danger text-uppercase fw-bold">demo version</span>
                </nav>
            </div>

            <div class="p-3 pb-md-4 mx-auto text-center">
                <h1 class="display-4 fw-normal">Keep your confidential data</h1>
                <p class="fs-5 text-muted">The data is encrypted and decrypted on your computer using the specified password. A complex key will then be created so that the next time the server can find your data and return it in encrypted form. You will need to re-enter the password to view and overwrite the data.</p>
            </div>
        </header>

        <main>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password" placeholder="Password*">
                        <label for="password">Password*</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="key" placeholder="Key">
                        <label for="key">Key</label>
                    </div>
                </div>
                <div class="col-md-6">

                </div>
                <div class="col-md-12">
                    <div class="form-floating">
                        <textarea class="form-control" placeholder="Your secret data*" id="data" style="height: 200px"></textarea>
                        <label for="data">Your secret data*</label>
                    </div>
                </div>

                <div class="col-md-12 text-center mt-4">
                    <button type="button" class="btn btn-warning" id="write-btn">Save & Write</button>
                    <button type="button" class="btn btn-dark" id="read-btn">Read</button>
                </div>

                <div class="col-md-12 text-center mt-4">
                    <div class="form-floating">
                        <textarea class="form-control" placeholder="Generated key" readonly id="key-result" style="height: 100px"></textarea>
                        <label for="key-result">Generated key</label>
                    </div>
                </div>
            </div>
        </main>

        <footer class="pt-4 my-md-5 pt-md-5 border-top">
            <div class="row">
                <div class="col-12 col-md">
                    <small class="d-block mb-3 text-muted">&copy; 2021 Security Cloud</small>
                </div>
            </div>
        </footer>
    </div>

    <script>

        var passwordField = document.getElementById('password');
        var keyField = document.getElementById('key');
        var dataField = document.getElementById('data');
        var keyResult = document.getElementById('key-result');

        {{-- Шифрування даних --}}
        function Crypt(data = '', password = '', key = null) {
            axios.post('{{ route('security.cloud.crypt') }}', {
                data: CryptoJS.AES.encrypt(data, password).toString(),
                key: key
            }).then(function (result) {
                if(result.status === 200) {
                    keyResult.value = result.data.key

                    if( keyField.value === '') {
                        keyField.value = result.data.key
                    }
                    alert('Saved');
                }
                else {
                    alert('Access denied!')
                }
            });
        }

        {{-- Дешифрування даних --}}
        function Decrypt(password = '', key = null) {
            axios.post('{{ route('security.cloud.get') }}', {
                key: key
            }).then(function (result) {
                if(result.status === 200) {
                    const data = CryptoJS.AES.decrypt(result.data.data, password).toString(CryptoJS.enc.Utf8);
                    if(data === '') {
                        dataField.value = 'Bad password or key';
                    } else {
                        dataField.value = data;
                    }
                }
                else {
                    alert('Access denied!')
                }
            });
        }

        document.getElementById('read-btn').addEventListener('click', function (e) {

            if(keyField.value === '') {
                alert('Please insert key');
                return 0;
            }
            else if(passwordField.value === '') {
                alert('Please insert password');
                return 0;
            }

            Decrypt(passwordField.value, keyField.value)
        });

        document.getElementById('write-btn').addEventListener('click', function (e) {

            if(keyField.value !== '') {
                if(!confirm('Rewrite data?')) {
                    return 0;
                }
            }

            if(passwordField.value === '') {
                alert('Please insert password');
                return 0;
            }

            if(dataField.value === '') {
                alert('Please insert data');
                return 0;
            }

            Crypt(dataField.value, passwordField.value, keyField.value);
        });

    </script>
    </body>
</html>
