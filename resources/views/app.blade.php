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
    let name = document.getElementById('name').value;
    let email = document.getElementById('email').value;
    let password = document.getElementById('password').value;

    if (!name || !email || !password) {
        showMessage("All fields are required", "error");
        return;
    }

    fetch('/api/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
        })
    })
    .then(res => res.json().then(data => ({ status: res.status, body: data })))
    .then(result => {
        console.log(result);

        if (result.status === 200 || result.status === 201) {
            showMessage("Registered successfully", "success");
        } else {
            showMessage(result.body.message || "Registration failed", "error");
        }
    })
    .catch(err => {
        console.log(err);
        showMessage("Something went wrong", "error");
    });
}

function login() {

    let email = document.getElementById('login_email').value;
    let password = document.getElementById('login_password').value;

    // Frontend validation
    if (!email || !password) {
        showMessage("Email and password are required", "error");
        return;
    }

    fetch('/api/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            email: email,
            password: password
        })
    })
    .then(res => res.json().then(data => ({ status: res.status, body: data })))
    .then(result => {
        console.log(result);

        // Success case
        if (result.status === 200) {

            if (!result.body.token) {
                showMessage("Invalid login response", "error");
                return;
            }

            localStorage.setItem('token', result.body.token);
            localStorage.setItem('user_id', result.body.user.id);

            showMessage("Login successful", "success");

            loadUser(); // update UI
            getWallet();
            getTransactions();
            document.getElementById('login_email').value = "";
            document.getElementById('login_password').value = "";
        }

        // Invalid credentials
        else if (result.status === 401) {
            showMessage("Invalid email or password", "error");
        }

        // Validation error
        else if (result.status === 422) {
            showMessage(result.body.message || "Validation failed", "error");
        }

        // Other errors
        else {
            showMessage("Login failed", "error");
        }
    })
    .catch(err => {
        console.log(err);
        showMessage("Something went wrong", "error");
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

    let receiver = document.getElementById('receiver').value;
    let amount = document.getElementById('amount').value;

    // Frontend validation
    if (!receiver || !amount) {
        showMessage("All fields are required", "error");
        return;
    }

    if (isNaN(amount) || amount <= 0) {
        showMessage("Amount must be greater than 0", "error");
        return;
    }

    let currentUserId = localStorage.getItem('user_id');

    // (optional if you later pass user_id or email check)
    // prevent self-transfer (if backend supports email match)
    // you can improve later using user email instead of ID

    fetch('/api/transfer', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        body: JSON.stringify({
            email: receiver,
            amount: amount
        })
    })
    .then(res => res.json().then(data => ({ status: res.status, body: data })))
    .then(result => {
        console.log(result);

        // Success
        if (result.status === 200) {
            showMessage("Transfer successful", "success");

            // Refresh UI
            getWallet();
            getTransactions();

            // clear fields
            document.getElementById('receiver').value = "";
            document.getElementById('amount').value = "";
        }

        // Validation errors
        else if (result.status === 422) {
            showMessage(result.body.message || "Validation failed", "error");
        }

        // Insufficient balance (if backend sends message)
        else if (result.status === 400) {
            showMessage(result.body.message || "Insufficient balance", "error");
        }

        // Unauthorized
        else if (result.status === 401) {
            showMessage("Please login again", "error");
        }

        // Other errors
        else {
            showMessage("Transfer failed", "error");
        }
    })
    .catch(err => {
        console.log(err);
        showMessage("Something went wrong", "error");
    });
}

function getTransactions() {
    console.log("TOKEN:", localStorage.getItem('token'));
    fetch('/api/transactions', {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        }
    })
    .then(res => {
        console.log("STATUS:", res.status);

        return res.text().then(text => {
            console.log("RAW RESPONSE:", text); // THIS WILL SHOW ERROR

            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error("Not JSON response");
            }
        });
    })
    .then(data => {
        let container = document.getElementById('transactions');
        container.innerHTML = "";

        let userId = localStorage.getItem('user_id');

        if (!Array.isArray(data)) {
            console.error("API ERROR:", data);
            alert("Error: " + (data.error || data.message));
            return;
        }
        
        data.forEach(tx => {
            let div = document.createElement('div');

            let isSender = tx.sender_id == userId;

            let typeClass = isSender ? "sent" : "received";
            let label = isSender ? "Sent" : "Received";
            let otherUser = isSender ? (tx.receiver && tx.receiver.email || "Unknown") : (tx.sender && tx.sender.email || "Unknown");

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
