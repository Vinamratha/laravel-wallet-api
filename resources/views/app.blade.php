<!DOCTYPE html>

<html>
<head>
    <title>Wallet App</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f7fa;
            display: flex;
            justify-content: center;
            margin-top: 50px;
        }
        .container {
            width: 400px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-top: 20px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            margin-top: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        .message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            display: none;
        }
        .success { background: #d4edda; }
        .error { background: #f8d7da; }
    </style>
</head>

<body>
<div class="container">

<h1>Wallet App</h1>

<div id="userInfo"></div>

<h2>Register</h2>
<input id="name" placeholder="Name">
<input id="email" placeholder="Email">
<input id="password" type="password" placeholder="Password">
<button onclick="register()">Register</button>

<h2>Login</h2>
<input id="login_email" placeholder="Email">
<input id="login_password" type="password" placeholder="Password">
<button onclick="login()">Login</button>

<button onclick="logout()">Logout</button>

<h2>Wallet</h2>
<button onclick="getWallet()">Get Balance</button>
<p id="balance"></p>

<h2>Transfer</h2>
<input id="receiver" placeholder="Receiver Email">
<input id="amount" placeholder="Amount">
<button onclick="transfer()">Send</button>

<h2>Transactions</h2>
<button onclick="getTransactions()">Load</button>
<ul id="transactions"></ul>

<div id="msg" class="message"></div>
</div>

<script>
let token = localStorage.getItem('token') || "";

function showMessage(text, type="success") {
    let msg = document.getElementById('msg');
    msg.innerText = text;
    msg.className = "message " + type;
    msg.style.display = "block";
}

function register() {
    fetch('/api/register', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            name: name.value,
            email: email.value,
            password: password.value
        })
    })
    .then(res => res.json())
    .then(data => showMessage("Registered successfully"));
}

function login() {
    fetch('/api/login', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            email: login_email.value,
            password: login_password.value
        })
    })
    .then(res => res.json())
    .then(data => {
        localStorage.setItem('token', data.token);
        localStorage.setItem('user_id', data.user.id);
        token = data.token;
        showMessage("Logged in");
        loadUser();
    });
}

function logout() {
    localStorage.clear();
    showMessage("Logged out");
}

function loadUser() {
    document.getElementById('userInfo').innerText =
        "Logged in User ID: " + localStorage.getItem('user_id');
}

function getWallet() {
    fetch('/api/wallet', {
        headers: { 'Authorization': 'Bearer ' + token }
    })
    .then(res => res.json())
    .then(data => {
        balance.innerText = "Balance: ₹" + data.balance;
    });
}

function transfer() {
    fetch('/api/transfer', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({
            email: receiver.value,
            amount: amount.value
        })
    })
    .then(res => res.json())
    .then(data => showMessage("Transfer successful"));
}

function getTransactions() {
    fetch('/api/transactions', {
        headers: { 'Authorization': 'Bearer ' + token }
    })
    .then(res => res.json())
    .then(data => {
        let list = document.getElementById('transactions');
        list.innerHTML = "";

        data.forEach(tx => {
            let item = document.createElement('li');
            item.innerText = `₹${tx.amount} | From ${tx.sender_id} → ${tx.receiver_id}`;
            list.appendChild(item);
        });
    });
}

if (token) loadUser();
</script>

</body>
</html>
