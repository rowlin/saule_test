<div class="container">
    <header class="header">
        <h1>Admin Panel</h1>
        <div class="header-info">
            <span id="adminName"></span>
            <button class="btn btn-sm btn-logout" onclick="logout()">Logout</button>
        </div>
    </header>

    <div class="admin-tabs">
        <button class="tab-btn active" data-tab="users">Users</button>
        <button class="tab-btn" data-tab="events">Events</button>
        <button class="tab-btn" data-tab="bets">Bets</button>
        <button class="tab-btn" data-tab="settle">Settle Bets</button>
        <button class="tab-btn" data-tab="rates">Exchange Rates</button>
        <button class="tab-btn" data-tab="logs">Logs</button>
    </div>

    <div class="tab-content active" id="tab-users">
        <div class="card">
            <h2>Users</h2>
            <div id="usersContainer"></div>

        </div>
    </div>

    <div class="tab-content" id="tab-events">
        <div class="card">
            <h2>Available Events</h2>
            <div id="eventsContainer"></div>
        </div>
        <div class="card">
            <h2>Add New Event</h2>
            <form id="addEventForm">
                <div class="form-group">
                    <label for="eventName">Event Name</label>
                    <input type="text" id="eventName" placeholder="e.g. Team A - Team B" required>
                </div>
                <div style="display:flex;gap:15px;flex-wrap:wrap">
                    <div class="form-group" style="flex:1;min-width:120px">
                        <label for="eventTeam1">1 (Home Win)</label>
                        <input type="number" id="eventTeam1" step="0.01" min="1.01" max="40" placeholder="2.50" required>
                    </div>
                    <div class="form-group" style="flex:1;min-width:120px">
                        <label for="eventDraw">X (Draw)</label>
                        <input type="number" id="eventDraw" step="0.01" min="1.01" max="40" placeholder="3.05" required>
                    </div>
                    <div class="form-group" style="flex:1;min-width:120px">
                        <label for="eventTeam2">2 (Away Win)</label>
                        <input type="number" id="eventTeam2" step="0.01" min="1.01" max="40" placeholder="3.15" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Create Event</button>
                <span class="alert alert-success" id="eventSuccess" style="display:none;margin-left:10px"></span>
                <span class="alert alert-error" id="eventError" style="display:none;margin-left:10px"></span>
            </form>
        </div>
    </div>

    <div class="tab-content" id="tab-bets">
        <div class="card">
            <h2>All Bets</h2>
            <div id="allBetsContainer"></div>
        </div>
    </div>

    <div class="tab-content" id="tab-settle">
        <div class="card">
            <h2>Settle Bets</h2>
            <div id="pendingBetsContainer"></div>
        </div>
    </div>

    <div class="tab-content" id="tab-rates">
        <div class="card">
            <h2>Exchange Rates (base: EUR)</h2>
            <form id="ratesForm">
                <div class="form-group">
                    <label for="rateEUR_USD">EUR → USD</label>
                    <input type="number" id="rateEUR_USD" step="0.000001" min="0.000001" required>
                </div>
                <div class="form-group">
                    <label for="rateEUR_RUB">EUR → RUB</label>
                    <input type="number" id="rateEUR_RUB" step="0.000001" min="0.000001" required>
                </div>
                <p style="font-size:13px;color:#888">Reverse rates are calculated automatically.</p>
                <button type="submit" class="btn btn-primary">Save Rates</button>
            </form>
            <div class="alert alert-success" id="ratesSuccess" style="display:none"></div>
            <div class="alert alert-error" id="ratesError" style="display:none"></div>
        </div>
    </div>

    <div class="tab-content" id="tab-logs">
        <div class="card">
            <h2>Balance Change Logs</h2>
            <div class="form-group">
                <label for="logUserId">Filter by User</label>
                <select id="logUserId">
                    <option value="">All users</option>
                </select>
            </div>
            <div id="logsContainer"></div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadAdminName();
    loadUsers();
    loadEvents();
    loadAllBets();
    loadPendingBets();
    loadRates();
    loadLogUserSelect();
    loadAllLogs();

    var tabFromUrl = new URLSearchParams(window.location.search).get('tab');
    if (tabFromUrl) {
        activateTab(tabFromUrl);
    }

    $('.tab-btn').on('click', function() {
        var tab = $(this).data('tab');
        activateTab(tab);
        var url = new URL(window.location);
        url.searchParams.set('tab', tab);
        history.pushState(null, '', url);
    });

    function activateTab(tab) {
        $('.tab-btn').removeClass('active');
        $('.tab-btn[data-tab="' + tab + '"]').addClass('active');
        $('.tab-content').removeClass('active');
        $('#tab-' + tab).addClass('active');
        if (tab === 'events') {
            loadEvents();
        }
        if (tab === 'rates') {
            loadRates();
        }
        if (tab === 'logs') {
            loadAllLogs();
        }
    }

    $('#ratesForm').on('submit', function(e) {
        e.preventDefault();
        saveRates();
    });

    $('#addEventForm').on('submit', function(e) {
        e.preventDefault();
        $('#eventSuccess').hide();
        $('#eventError').hide();

        var name = $('#eventName').val().trim();
        var team1 = parseFloat($('#eventTeam1').val());
        var draw = parseFloat($('#eventDraw').val());
        var team2 = parseFloat($('#eventTeam2').val());

        if (!name) {
            $('#eventError').text('Event name is required').show();
            return;
        }
        if (!team1 || team1 < 1.01 || !draw || draw < 1.01 || !team2 || team2 < 1.01) {
            $('#eventError').text('Odds must be at least 1.01').show();
            return;
        }

        $.post('/api/addEvent', {
            name: name,
            team1Win: team1,
            draw: draw,
            team2Win: team2
        }, function(response) {
            if (response.success) {
                $('#eventSuccess').text('Event created!').show();
                $('#addEventForm')[0].reset();
                loadEvents();
            }
        }).fail(function(xhr) {
            var err = xhr.responseJSON ? xhr.responseJSON.error : 'Failed to create event';
            $('#eventError').text(err).show();
        });
    });
});

function loadAdminName() {
    $.get('/api/profile', function(user) {
        $('#adminName').text('Admin: ' + user.name);
    });
}

function loadEvents() {
    $.get('/api/events', function(events) {
        if (!events.length) {
            $('#eventsContainer').html('<p>No events yet.</p>');
            return;
        }
        var html = '<div style="overflow-x:auto"><table class="data-table"><thead><tr>' +
            '<th>ID</th><th>Event</th><th>1</th><th>X</th><th>2</th><th>Status</th><th>Created</th>' +
            '</tr></thead><tbody>';
        events.forEach(function(e) {
            html += '<tr>' +
                '<td>' + e.id + '</td>' +
                '<td>' + e.name + '</td>' +
                '<td>' + parseFloat(e.team1_win).toFixed(2) + '</td>' +
                '<td>' + parseFloat(e.draw).toFixed(2) + '</td>' +
                '<td>' + parseFloat(e.team2_win).toFixed(2) + '</td>' +
                '<td>' + e.status + '</td>' +
                '<td>' + e.created_at + '</td>' +
                '</tr>';
        });
        html += '</tbody></table></div>';
        $('#eventsContainer').html(html);
    });
}

function loadUsers() {
    $.get('/api/users', function(users) {
        var html = '<table class="data-table" id="usersTable"><thead><tr>' +
            '<th>ID</th><th>Login</th><th>Name</th><th>Status</th><th>Role</th><th>Balance</th><th>Actions</th>' +
            '</tr></thead><tbody>';
        users.forEach(function(u) {
            var userId = u.id;
            var currency = u.default_currency;
            html += '<tr id="userRow-' + userId + '">' +
                '<td>' + userId + '</td>' +
                '<td>' + u.login + '</td>' +
                '<td>' + u.name + '</td>' +
                '<td>' + u.status + '</td>' +
                '<td>' + (u.is_admin ? 'Admin' : 'User') + '</td>' +
                '<td>' + parseFloat(u.balance).toFixed(2) + ' ' + currency + '</td>' +
                '<td class="actions">' +
                '<button class="btn btn-sm btn-primary" onclick="toggleContacts(' + userId + ',\'' + u.name + '\')">Contacts Info</button> ' +
                '<button class="btn btn-sm btn-success" onclick="toggleBalance(' + userId + ',\'' + u.name + '\',\'' + currency + '\')">Edit Balance</button>' +
                '</td>' +
                '</tr>';
        });
        html += '</tbody></table>';
        $('#usersContainer').html(html);
    });
}

function removeDetailRow(userId) {
    $('#detailRow-' + userId).remove();
}

function showDetailRow(userId, title, content) {
    removeDetailRow(userId);
    var html = '<tr id="detailRow-' + userId + '" class="detail-row">' +
        '<td colspan="7" style="padding:16px;background:#f8f9fa">' +
        '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">' +
        '<strong>' + title + '</strong>' +
        '<button class="btn btn-sm" onclick="removeDetailRow(' + userId + ')">X</button>' +
        '</div>' +
        '<div id="detailContent-' + userId + '">' + content + '</div>' +
        '</td>' +
        '</tr>';
    $('#userRow-' + userId).after(html);
}

// --- Inline Contacts ---

function toggleContacts(userId, userName) {
    if ($('#detailRow-' + userId).length) {
        removeDetailRow(userId);
        return;
    }
    removeDetailRow(userId);
    showDetailRow(userId, 'Contacts — ' + userName, '<div id="detailContacts-' + userId + '">Loading...</div>');
    loadAdminContacts(userId);
}

function loadAdminContacts(userId) {
    $.get('/api/adminUserContacts?user_id=' + userId, function(contacts) {
        var html = '<div class="contacts-list">';
        if (contacts.length) {
            contacts.forEach(function(c) {
                html += '<div class="contact-item">' +
                    '<span class="contact-type">' + c.type + '</span>' +
                    '<span class="contact-value">' + c.value + '</span>' +
                    '<button class="btn btn-sm btn-danger" onclick="adminDeleteContact(' + c.id + ',' + userId + ')">Delete</button>' +
                    '</div>';
            });
        } else {
            html += '<p>No contacts.</p>';
        }
        html += '</div>';
        html += '<div class="add-contact-form" style="margin-top:12px">' +
            '<select id="adminContactType-' + userId + '">' +
            '<option value="phone">Phone</option>' +
            '<option value="email">Email</option>' +
            '</select> ' +
            '<input type="text" id="adminContactValue-' + userId + '" placeholder="Enter value"> ' +
            '<button class="btn btn-sm btn-primary" onclick="adminAddContact(' + userId + ')">Add</button>' +
            '<span class="error" id="adminContactError-' + userId + '"></span>' +
            '</div>';
        $('#detailContacts-' + userId).html(html);
    });
}

function adminAddContact(userId) {
    var type = $('#adminContactType-' + userId).val();
    var value = $('#adminContactValue-' + userId).val().trim();
    $('#adminContactError-' + userId).text('');

    if (!value) {
        $('#adminContactError-' + userId).text('Value is required');
        return;
    }

    $.post('/api/adminAddContact', {
        userId: userId,
        type: type,
        value: value
    }, function(response) {
        if (response.success) {
            $('#adminContactValue-' + userId).val('');
            loadAdminContacts(userId);
        }
    }).fail(function(xhr) {
        var err = xhr.responseJSON ? xhr.responseJSON.error : 'Failed';
        $('#adminContactError-' + userId).text(err);
    });
}

function adminDeleteContact(contactId, userId) {
    $.post('/api/adminDeleteContact', {
        id: contactId,
        userId: userId
    }, function() {
        loadAdminContacts(userId);
    }).fail(function(xhr) {
        alert(xhr.responseJSON ? xhr.responseJSON.error : 'Failed to delete');
    });
}

// --- Inline Balance ---

function toggleBalance(userId, userName, currency) {
    if ($('#detailRow-' + userId).length) {
        removeDetailRow(userId);
        return;
    }
    removeDetailRow(userId);
    showDetailRow(userId, 'Balance — ' + userName, '<div id="detailBalance-' + userId + '">Loading...</div>');
    loadUserBalance(userId, currency);
}

function loadUserBalance(userId, currency) {
    $.get('/api/userBalances/' + userId, function(balances) {
        var bal = null;
        for (var i = 0; i < balances.length; i++) {
            if (balances[i].currency === currency) {
                bal = balances[i];
                break;
            }
        }
        var currentAmount = bal ? parseFloat(bal.amount).toFixed(2) : '0.00';
        var html = '<div style="margin-bottom:15px;font-size:18px;font-weight:600" id="balDisplay-' + userId + '">' +
            currency + ': ' + currentAmount +
            '</div>';
        html += '<div class="form-group" data-balance-currency="' + currency + '" data-balance-amount="' + currentAmount + '">' +
            '<label>Currency</label>' +
            '<select id="balCurrency-' + userId + '" required onchange="onCurrencyChange(' + userId + ', this.value)">' +
<?php foreach (\Enums\Currency::values() as $c): ?>            '<option value="<?= $c ?>"' + (currency === '<?= $c ?>' ? ' selected' : '') + '><?= $c ?></option>' +
<?php endforeach; ?>            '</select>' +
            '</div>';
        html += '<div class="form-group">' +
            '<label>New Balance</label>' +
            '<input type="number" id="balAmount-' + userId + '" step="0.01" min="0" value="' + currentAmount + '" required>' +
            '</div>' +
            '<button class="btn btn-primary" onclick="updateBalance(' + userId + ',\'' + currency + '\')">Set Balance</button>' +
            '<span class="alert alert-success" id="balSuccess-' + userId + '" style="display:none;margin-left:10px"></span>' +
            '<span class="alert alert-error" id="balError-' + userId + '" style="display:none;margin-left:10px"></span>';
        $('#detailBalance-' + userId).html(html);
    });
}

function onCurrencyChange(userId, selectedCurrency) {
    var $group = $('#balCurrency-' + userId).closest('.form-group');
    var sourceCurrency = $group.data('balance-currency');
    var sourceAmount = parseFloat($group.data('balance-amount'));

    if (selectedCurrency === sourceCurrency) {
        $('#balAmount-' + userId).val(sourceAmount.toFixed(2));
        $('#balDisplay-' + userId).text(sourceCurrency + ': ' + sourceAmount.toFixed(2));
        return;
    }

    $.get('/api/rates', function(rates) {
        var rateMap = {};
        rates.forEach(function(r) {
            rateMap[r.from + '_' + r.to] = r.rate;
        });

        var rate = null;
        var key = sourceCurrency + '_' + selectedCurrency;
        if (rateMap[key] !== undefined) {
            rate = rateMap[key];
        } else {
            var reverseKey = selectedCurrency + '_' + sourceCurrency;
            if (rateMap[reverseKey] !== undefined) {
                rate = 1 / rateMap[reverseKey];
            }
        }

        var converted = rate !== null ? (sourceAmount * rate) : sourceAmount;
        $('#balAmount-' + userId).val(converted.toFixed(2));
        $('#balDisplay-' + userId).text(selectedCurrency + ': ' + converted.toFixed(2));
    });
}

function updateBalance(userId, currency) {
    var selectedCurrency = $('#balCurrency-' + userId).val();
    var raw = $('#balAmount-' + userId).val().trim();
    var newBalance = parseFloat(raw);

    $('#balSuccess-' + userId).hide();
    $('#balError-' + userId).hide();

    if (raw === '' || isNaN(newBalance) || newBalance < 0) {
        $('#balError-' + userId).text('Enter a valid non-negative balance').show();
        return;
    }

    $.post('/api/setBalance', {
        userId: userId,
        currency: selectedCurrency,
        balance: newBalance
    }, function(response) {
        if (response.success) {
            $('#balSuccess-' + userId).text('Updated!').show();
            $('#balAmount-' + userId).val(newBalance.toFixed(2));
            loadUserBalance(userId, currency);
            loadUsers();
        } else {
            $('#balError-' + userId).text(response.error || 'Failed to update balance').show();
        }
    }).fail(function(xhr) {
        var err = xhr.responseJSON ? xhr.responseJSON.error : 'Failed to update balance';
        $('#balError-' + userId).text(err).show();
    });
}

// --- Exchange Rates tab ---
function loadRates() {
    $.get('/api/rates', function(rates) {
        rates.forEach(function(r) {
            var key = r.from + '_' + r.to;
            if (key === 'EUR_USD' || key === 'EUR_RUB') {
                $('#rate' + key).val(r.rate);
            }
        });
    });
}

function saveRates() {
    var eurUsd = parseFloat($('#rateEUR_USD').val());
    var eurRub = parseFloat($('#rateEUR_RUB').val());

    if (!eurUsd || !eurRub) return;

    $('#ratesSuccess').hide();
    $('#ratesError').hide();

    $.post('/api/updateRates', {
        EUR_USD: eurUsd,
        EUR_RUB: eurRub
    }, function(response) {
        if (response.success) {
            $('#ratesSuccess').text('Rates updated successfully!').show();
        }
    }).fail(function(xhr) {
        var err = xhr.responseJSON ? xhr.responseJSON.error : 'Failed to save rates';
        $('#ratesError').text(err).show();
    });
}

// --- Logs tab ---
function loadLogUserSelect() {
    $.get('/api/users', function(users) {
        var html = '<option value="">All users</option>';
        users.forEach(function(u) {
            html += '<option value="' + u.id + '">' + u.name + ' (' + u.login + ')</option>';
        });
        $('#logUserId').html(html);
    });
}

$('#logUserId').on('change', function() {
    loadAllLogs();
});

function loadAllLogs() {
    var userId = $('#logUserId').val();
    var url = '/api/adminLogs';
    if (userId) {
        url += '?user_id=' + userId;
    }

    $.get(url, function(logs) {
        if (!logs.length) {
            $('#logsContainer').html('<p>No logs available.</p>');
            return;
        }
        var html = '<div style="overflow-x:auto"><table class="data-table"><thead><tr>' +
            '<th>ID</th><th>User</th><th>Action</th><th>Currency</th><th>Change</th><th>Balance Before</th><th>Balance After</th><th>Admin</th><th>Note</th><th>Date</th>' +
            '</tr></thead><tbody>';
        logs.forEach(function(l) {
            var changeClass = parseFloat(l.amount) < 0 ? 'status-lost' : (parseFloat(l.amount) > 0 ? 'status-won' : '');
            html += '<tr>' +
                '<td>' + l.id + '</td>' +
                '<td>' + l.user_name + '</td>' +
                '<td>' + l.action + '</td>' +
                '<td>' + l.currency + '</td>' +
                '<td class="' + changeClass + '">' + parseFloat(l.amount).toFixed(2) + '</td>' +
                '<td>' + parseFloat(l.balance_before).toFixed(2) + '</td>' +
                '<td>' + parseFloat(l.balance_after).toFixed(2) + '</td>' +
                '<td>' + (l.admin_name || '-') + '</td>' +
                '<td>' + (l.note || '') + '</td>' +
                '<td>' + l.created_at + '</td>' +
                '</tr>';
        });
        html += '</tbody></table></div>';
        $('#logsContainer').html(html);
    });
}

function loadAllBets() {
    $.get('/api/bets', function(bets) {
        if (!bets.length) {
            $('#allBetsContainer').html('<p>No bets placed yet.</p>');
            return;
        }
        var html = '<div style="overflow-x:auto"><table class="data-table"><thead><tr>' +
            '<th>ID</th><th>User</th><th>Event</th><th>Outcome</th><th>Odds</th><th>Amount</th><th>Status</th><th>Date</th>' +
            '</tr></thead><tbody>';
        bets.forEach(function(b) {
            var outcomeLabels = { team1_win: '1', draw: 'X', team2_win: '2' };
            var statusClass = b.status === 'won' ? 'status-won' : (b.status === 'lost' ? 'status-lost' : 'status-pending');
            html += '<tr>' +
                '<td>' + b.id + '</td>' +
                '<td>' + b.user_name + ' (' + b.user_login + ')</td>' +
                '<td>' + b.event_name + '</td>' +
                '<td>' + (outcomeLabels[b.outcome] || b.outcome) + '</td>' +
                '<td>' + parseFloat(b.odds).toFixed(2) + '</td>' +
                '<td>' + parseFloat(b.amount).toFixed(2) + ' ' + b.currency + '</td>' +
                '<td class="' + statusClass + '">' + b.status + '</td>' +
                '<td>' + b.created_at + '</td>' +
                '</tr>';
        });
        html += '</tbody></table></div>';
        $('#allBetsContainer').html(html);
    });
}

function loadPendingBets() {
    $.get('/api/bets', function(bets) {
        var pending = bets.filter(function(b) { return b.status === 'pending'; });
        if (!pending.length) {
            $('#pendingBetsContainer').html('<p>No pending bets.</p>');
            return;
        }
        var html = '<div style="overflow-x:auto"><table class="data-table"><thead><tr>' +
            '<th>ID</th><th>User</th><th>Event</th><th>Outcome</th><th>Odds</th><th>Amount</th><th>Date</th><th>Action</th>' +
            '</tr></thead><tbody>';
        pending.forEach(function(b) {
            var outcomeLabels = { team1_win: '1', draw: 'X', team2_win: '2' };
            html += '<tr>' +
                '<td>' + b.id + '</td>' +
                '<td>' + b.user_name + ' (' + b.user_login + ')</td>' +
                '<td>' + b.event_name + '</td>' +
                '<td>' + (outcomeLabels[b.outcome] || b.outcome) + '</td>' +
                '<td>' + parseFloat(b.odds).toFixed(2) + '</td>' +
                '<td>' + parseFloat(b.amount).toFixed(2) + ' ' + b.currency + '</td>' +
                '<td>' + b.created_at + '</td>' +
                '<td>' +
                '<button class="btn btn-sm btn-success" onclick="settleBet(' + b.id + ', \'won\')">Win</button> ' +
                '<button class="btn btn-sm btn-danger" onclick="settleBet(' + b.id + ', \'lost\')">Lose</button>' +
                '</td>' +
                '</tr>';
        });
        html += '</tbody></table></div>';
        $('#pendingBetsContainer').html(html);
    });
}

function settleBet(betId, result) {
    $.post('/api/settleBet', {
        betId: betId,
        result: result
    }, function(response) {
        if (response.success) {
            loadPendingBets();
            loadAllBets();
        }
    }).fail(function(xhr) {
        alert(xhr.responseJSON ? xhr.responseJSON.error : 'Failed to settle bet');
    });
}

function logout() {
    $.post('/login/logout', {}, function() {
        window.location.href = '/login';
    });
}
</script>
