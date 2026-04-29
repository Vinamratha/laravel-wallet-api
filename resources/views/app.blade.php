<!DOCTYPE html>
<html>
<head>
    <title>Wallet App</title>
</head>
<body>

<h2>Register</h2>
<input id="name" placeholder="Name"><br>
<input id="email" placeholder="Email"><br>
<input id="password" placeholder="Password" type="password"><br>
<button onclick="register()">Register</button>

<h2>Login</h2>
<input id="login_email" placeholder="Email"><br>
<input id="login_password" placeholder="Password" type="password"><br>
<button onclick="login()">Login</button>

<h2>Wallet</h2>
<button onclick="getWallet()">Get Balance</button>
<p id="balance"></p>

<h2>Transfer</h2>
<input id="receiver" placeholder="Receiver Email"><br>
<input id="amount" placeholder="Amount"><br>
<button onclick="transfer()">Send</button>

<h2>Transaction History</h2>
<button onclick="getTransactions()">Load Transactions</button>
<ul id="transactions"></ul>

<button onclick="logout()">Logout</button>

<script>
let token = localStorage.getItem('token') || "";

if (localStorage.getItem('token')) {
    document.body.insertAdjacentHTML('afterbegin', '<p>Logged in</p>');
}

function register() {
    fetch('/api/register', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
        })
    })
    .then(res => res.json())
    .then(data => alert(JSON.stringify(data)));
}

function login() {
    fetch('/api/login', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            email: document.getElementById('login_email').value,
            password: document.getElementById('login_password').value
        })
    })
    .then(res => res.json())
    .then(data => {
        localStorage.setItem('token', data.token);
        localStorage.setItem('user_id', data.user.id);
        token = data.token;
        alert("Logged in");
    });
}

function getUserIdFromToken() {
    return localStorage.getItem('user_id');
}

function getWallet() {
    fetch('/api/wallet', {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        }
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('balance').innerText = "Balance: " + data.balance;
    });
}

function transfer() {
    fetch('/api/transfer', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        body: JSON.stringify({
            email: document.getElementById('receiver').value,
            amount: document.getElementById('amount').value
        })
    })
    .then(res => res.json())
    .then(data => alert(JSON.stringify(data)));
}

function getTransactions() {
    fetch('/api/transactions', {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        }
    })
    .then(res => res.json())
    .then(data => {
        let list = document.getElementById('transactions');
        list.innerHTML = "";

        data.forEach(tx => {
            let item = document.createElement('li');

            let text = "";

            if (tx.sender_id == getUserIdFromToken()) {
                text = "Sent ₹" + tx.amount + " → User ID " + tx.receiver_id;
            } else {
                text = "Received ₹" + tx.amount + " ← User ID " + tx.sender_id;
            }

            item.innerText = text;
            list.appendChild(item);
        });
    });
}

function logout() {
    localStorage.removeItem('token');
    alert("Logged out");
}

</script>

</body>
</html>