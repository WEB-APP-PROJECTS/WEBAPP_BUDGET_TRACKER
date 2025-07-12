<?php
session_start();
require 'connection.php';

// Check admin session
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php");
    exit();
}

// Queries for existing dashboard (unchanged)
$users = mysqli_query($conn, "SELECT id, username, email FROM users WHERE role = 'user'");
$categories = mysqli_query($conn, "SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_style.css" />
    <style>
        /* Inline styles for manage users section */
        #manageUsersSection {
            display: none;
            padding: 20px;
            border: 1px solid #ccc;
            margin: 20px 0;
            background: #f9f9f9;
        }
        #manageUsersSection table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        #manageUsersSection th, #manageUsersSection td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        #manageUsersSection button {
            margin-right: 5px;
        }
    </style>
</head>
<body>

<div class="admin-header">
    <div class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?></div>
    <div class="admin-buttons">
        <a href="admin_categories.php" class="btn">User Transactions</a>
        <button id="manageUsersBtn" class="btn">Manage Users</button>
        <a href="logout.php" class="btn">Logout</a>
    </div>
</div>

<div class="section">
    <h2>Registered Users</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<div class="section">
    <h2>Categories</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($categories)): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Manage Users Section (hidden by default) -->
<div id="manageUsersSection">
    <h3>Manage Users</h3>
    <table id="usersTable" border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <button id="closeManageUsers">Close</button>
</div>

<script>
document.getElementById('manageUsersBtn').addEventListener('click', () => {
    document.getElementById('manageUsersSection').style.display = 'block';
    loadUsers();
});

document.getElementById('closeManageUsers').addEventListener('click', () => {
    document.getElementById('manageUsersSection').style.display = 'none';
});

function loadUsers() {
    fetch('fetch_users.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert('Error loading users: ' + data.message);
                return;
            }
            const tbody = document.querySelector('#usersTable tbody');
            tbody.innerHTML = '';
            data.users.forEach(user => {
                const tr = document.createElement('tr');
                tr.dataset.userId = user.id;

                tr.innerHTML = `
                    <td>${user.id}</td>
                    <td class="username-text">${escapeHtml(user.username)}</td>
                    <td>${escapeHtml(user.email)}</td>
                    <td>
                        <button class="edit-btn">Edit</button>
                        <button class="save-btn" style="display:none;">Save</button>
                        <button class="cancel-btn" style="display:none;">Cancel</button>
                    </td>
                `;

                tbody.appendChild(tr);
            });

            setupEditButtons();
        })
        .catch(() => alert('Failed to fetch users'));
}

function setupEditButtons() {
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.onclick = () => {
            const tr = btn.closest('tr');
            const usernameCell = tr.querySelector('.username-text');
            const currentUsername = usernameCell.textContent;

            usernameCell.innerHTML = `<input type="text" class="edit-username" value="${escapeHtml(currentUsername)}">`;

            btn.style.display = 'none';
            tr.querySelector('.save-btn').style.display = 'inline-block';
            tr.querySelector('.cancel-btn').style.display = 'inline-block';
        };
    });

    document.querySelectorAll('.cancel-btn').forEach(btn => {
        btn.onclick = () => {
            const tr = btn.closest('tr');
            const usernameCell = tr.querySelector('.username-text');
            const input = usernameCell.querySelector('input');
            usernameCell.textContent = input.defaultValue;

            btn.style.display = 'none';
            tr.querySelector('.save-btn').style.display = 'none';
            tr.querySelector('.edit-btn').style.display = 'inline-block';
        };
    });

    document.querySelectorAll('.save-btn').forEach(btn => {
        btn.onclick = () => {
            const tr = btn.closest('tr');
            const userId = tr.dataset.userId;
            const newUsername = tr.querySelector('.edit-username').value.trim();

            if (!newUsername) {
                alert('Username cannot be empty');
                return;
            }

            btn.disabled = true;
            tr.querySelector('.cancel-btn').disabled = true;

            fetch('update_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId, username: newUsername }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    tr.querySelector('.username-text').textContent = newUsername;
                    btn.style.display = 'none';
                    tr.querySelector('.cancel-btn').style.display = 'none';
                    tr.querySelector('.edit-btn').style.display = 'inline-block';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(() => alert('Request failed'))
            .finally(() => {
                btn.disabled = false;
                tr.querySelector('.cancel-btn').disabled = false;
            });
        };
    });
}

function escapeHtml(text) {
    return text.replace(/[&<>"']/g, function(m) {
        return ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[m] || m;
    });
}
</script>

</body>
</html>
