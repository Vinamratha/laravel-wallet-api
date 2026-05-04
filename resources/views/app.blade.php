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
        .transaction {
            padding: 10px;
            margin-top: 8px;
            border-radius: 6px;
            font-size: 14px;
        }

        .sent {
            background: #ffe5e5;
            border-left: 4px solid red;
        }

        .received {
            background: #e5ffe5;
            border-left: 4px solid green;
        }

        .tx-amount {
            font-weight: bold;
        }

        .tx-time {
            font-size: 12px;
            color: gray;
        }
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
<div id="transactions"></div>

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
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        }
    })
    .then(res => res.json())
    .then(data => {
        let container = document.getElementById('transactions');
        container.innerHTML = "";

        let userId = localStorage.getItem('user_id');

        data.forEach(tx => {
            let div = document.createElement('div');

            let isSender = tx.sender_id == userId;

            let typeClass = isSender ? "sent" : "received";
            let label = isSender ? "Sent" : "Received";
            let otherUser = isSender ? (tx.receiver?.email || "Unknown") : (tx.sender?.email || "Unknown");

            let time = new Date(tx.created_at).toLocaleString();

            div.className = "transaction " + typeClass;

            div.innerHTML = `
                <div class="tx-amount">${label} ₹${tx.amount}</div>
                <div>${isSender ? "To" : "From"} User ID: ${otherUser}</div>
                <div class="tx-time">${time}</div>
            `;

            container.appendChild(div);
        });
    });
}

if (token) loadUser();
</script>

</body>
</html>
