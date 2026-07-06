<div class="container">
    <header class="header">
        <h1>Sports Betting</h1>
        <div class="header-info">
            <span id="userName"></span>
            <span id="headerBalance" class="header-balance"></span>
            <button class="btn btn-sm btn-logout" onclick="logout()">Logout</button>
        </div>
    </header>

    <div class="admin-tabs">
        <button class="tab-btn active" data-tab="betting">Betting</button>
        <button class="tab-btn" data-tab="profile">Profile</button>
        <button class="tab-btn" data-tab="mybets">My Bets</button>
        <button class="tab-btn" data-tab="payments">Payment History</button>
    </div>

    <div class="tab-content active" id="tab-betting">
        <section class="card">
            <h2>Available Events</h2>
            <div id="eventsContainer"></div>
        </section>
    </div>

    <div class="tab-content" id="tab-profile">
        <section class="card">
            <h2>My Profile</h2>
            <div id="profileInfo"></div>
            <h3>Contacts</h3>
            <div id="contactsContainer" class="contacts-list"></div>
            <div class="add-contact-form">
                <select id="contactType">
                    <option value="phone">Phone</option>
                    <option value="email">Email</option>
                </select>
                <input type="text" id="contactValue" placeholder="Enter value">
                <button class="btn btn-sm btn-primary" onclick="addContact()">Add</button>
                <span class="error" id="contactError"></span>
            </div>
        </section>
        <section class="card">
            <h2>Balance</h2>
            <div class="balances" id="balanceContainer"></div>
            <button class="btn btn-sm btn-primary" onclick="showChangeCurrency()" style="margin-top:10px">Change Currency</button>
            <div id="changeCurrencyForm" style="display:none;margin-top:10px">
                <select id="newCurrency">
<?php foreach (\Enums\Currency::values() as $c): ?>                    <option value="<?= $c ?>"><?= $c ?></option>
<?php endforeach; ?>                </select>
                <button class="btn btn-sm btn-success" onclick="changeCurrency()">Confirm</button>
                <button class="btn btn-sm" onclick="$('#changeCurrencyForm').hide()">Cancel</button>
                <span class="error" id="currencyError"></span>
            </div>
        </section>
    </div>

    <div class="tab-content" id="tab-mybets">
        <section class="card">
            <h2>My Bets</h2>
            <div id="myBetsContainer"></div>
        </section>
    </div>

    <div class="tab-content" id="tab-payments">
        <section class="card">
            <h2>Balance History</h2>
            <div id="logsContainer"></div>
        </section>
    </div>
</div>

<script>
$(document).ready(function() {
    loadProfile();
    loadBalance();
    loadEvents();
    loadMyBets();
    loadLogs();

    $('.admin-tabs .tab-btn').on('click', function() {
        var tab = $(this).data('tab');
        activateTab(tab);
    });

    function activateTab(tab) {
        $('.admin-tabs .tab-btn').removeClass('active');
        $('.admin-tabs .tab-btn[data-tab="' + tab + '"]').addClass('active');
        $('.tab-content').removeClass('active');
        $('#tab-' + tab).addClass('active');

        if (tab === 'betting') {
            loadEvents();
        }
        if (tab === 'mybets') {
            loadMyBets();
        }
        if (tab === 'payments') {
            loadLogs();
        }
    }
});

function loadProfile() {
    $.get('/api/profile', function(user) {
        window.userMaxBet = user.max_bet || 500;
        $('#userName').text('Welcome, ' + user.name);
        var html = '<p><strong>Login:</strong> ' + user.login + '</p>';
        html += '<p><strong>Name:</strong> ' + user.name + '</p>';
        html += '<p><strong>Gender:</strong> ' + user.gender + '</p>';
        html += '<p><strong>Birth:</strong> ' + user.birth_date + '</p>';
        if (user.address) {
            html += '<p><strong>Address:</strong> ' + user.address + '</p>';
        }
        $('#profileInfo').html(html);
        renderContacts(user.contacts);
    });
}

function renderContacts(contacts) {
    var html = '';
    if (contacts && contacts.length) {
        contacts.forEach(function(c) {
            html += '<div class="contact-item">' +
                '<span class="contact-type">' + c.type + '</span>' +
                '<span class="contact-value">' + c.value + '</span>' +
                '<button class="btn btn-sm btn-danger" onclick="deleteContact(' + c.id + ')">Delete</button>' +
                '</div>';
        });
    } else {
        html = '<p>No contacts added yet.</p>';
    }
    $('#contactsContainer').html(html);
}

function addContact() {
    var type = $('#contactType').val();
    var value = $('#contactValue').val().trim();
    $('#contactError').text('');

    if (!value) {
        $('#contactError').text('Value is required');
        return;
    }

    $.post('/api/addContact', {
        type: type,
        value: value
    }, function(response) {
        if (response.success) {
            $('#contactValue').val('');
            loadProfile();
        }
    }).fail(function(xhr) {
        var err = xhr.responseJSON ? xhr.responseJSON.error : 'Failed to add contact';
        $('#contactError').text(err);
    });
}

function deleteContact(id) {
    $.post('/api/deleteContact', { id: id }, function(response) {
        if (response.success) {
            loadProfile();
        }
    }).fail(function(xhr) {
        var err = xhr.responseJSON ? xhr.responseJSON.error : 'Failed to delete contact';
        alert(err);
    });
}

function loadBalance() {
    $.get('/api/balance', function(data) {
        var html = '<div class="balance-item">' +
            '<span class="currency">' + data.currency + '</span>' +
            '<span class="amount">' + data.amount.toFixed(2) + '</span>' +
            '</div>';
        var converted = [];
        $.each(data.converted, function(currency, amount) {
            converted.push(currency + ' ' + amount.toFixed(2));
        });
        if (converted.length) {
            html += '<p style="font-size:13px;color:#888;margin-top:5px">≈ ' + converted.join(' · ') + '</p>';
        }
        $('#balanceContainer').html(html);
        $('#headerBalance').text(data.amount.toFixed(2) + ' ' + data.currency);
    });
}

function showChangeCurrency() {
    $('#changeCurrencyForm').show();
    $('#currencyError').text('');
}

function changeCurrency() {
    var currency = $('#newCurrency').val();
    $('#currencyError').text('');

    $.post('/api/changeCurrency', { currency: currency }, function(response) {
        if (response.success) {
            $('#changeCurrencyForm').hide();
            loadBalance();
            loadProfile();
        }
    }).fail(function(xhr) {
        var err = xhr.responseJSON ? xhr.responseJSON.error : 'Failed to change currency';
        $('#currencyError').text(err);
    });
}

function loadEvents() {
    $.get('/api/events', function(events) {
        $.get('/api/myBets', function(bets) {
            var betMap = {};
            bets.forEach(function(b) {
                betMap[b.event_name] = b;
            });

            var outcomeLabels = { team1_win: '1', draw: 'X', team2_win: '2' };
            var html = '<table class="events-table"><thead><tr>' +
                '<th>Match</th><th>1</th><th>X</th><th>2</th><th>Bet</th>' +
                '</tr></thead><tbody>';
            events.forEach(function(e) {
                    var myBet = betMap[e.name];
                if (myBet && myBet.status !== 'pending') return;

                var betCell;
                if (myBet) {
                    betCell = '<div style="font-size:12px;line-height:1.6">' +
                        '<strong style="font-weight:700">' + parseFloat(myBet.amount).toFixed(2) + ' ' + myBet.currency + '</strong><br>' +
                        '<span>' + myBet.status + '</span>' +
                        '</div>';
                } else {
                    betCell = '<button class="btn btn-sm btn-primary" onclick="showInlineBet(' + e.id + ',\'' + e.name.replace(/'/g, "\\'") + '\',' + e.team1_win + ',' + e.draw + ',' + e.team2_win + ')">Bet</button>';
                }
                var t1 = parseFloat(e.team1_win);
                var dr = parseFloat(e.draw);
                var t2 = parseFloat(e.team2_win);
                var team1Class = (myBet && myBet.outcome === 'team1_win') ? ' class="chosen-outcome"' : '';
                var drawClass = (myBet && myBet.outcome === 'draw') ? ' class="chosen-outcome"' : '';
                var team2Class = (myBet && myBet.outcome === 'team2_win') ? ' class="chosen-outcome"' : '';
                html += '<tr id="eventRow-' + e.id + '">' +
                    '<td>' + e.name + '</td>' +
                    '<td' + team1Class + '>' + t1.toFixed(2) + '</td>' +
                    '<td' + drawClass + '>' + dr.toFixed(2) + '</td>' +
                    '<td' + team2Class + '>' + t2.toFixed(2) + '</td>' +
                    '<td>' + betCell + '</td>' +
                    '</tr>';
            });
            html += '</tbody></table>';
            $('#eventsContainer').html(html);
        });
    });
}

var selectedOutcome = {};

function showInlineBet(eventId, eventName, team1, draw, team2) {
    removeInlineBet(eventId);
    selectedOutcome[eventId] = null;
    var html = '<tr id="inlineBet-' + eventId + '" class="detail-row">' +
        '<td colspan="5" style="padding:16px;background:#f8f9fa">' +
        '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">' +
        '<strong>Place Bet: ' + eventName + '</strong>' +
        '<button class="btn btn-sm" onclick="removeInlineBet(' + eventId + ')">X</button>' +
        '</div>' +
        '<div class="form-group" style="margin-bottom:10px">' +
        '<label style="margin-bottom:8px;display:block">Select Outcome</label>' +
        '<button class="btn btn-sm outcome-btn" data-odds="' + team1 + '" onclick="selectOutcome(' + eventId + ',\'team1_win\',' + team1 + ')" style="margin-right:6px">1 (' + team1.toFixed(2) + ')</button>' +
        '<button class="btn btn-sm outcome-btn" data-odds="' + draw + '" onclick="selectOutcome(' + eventId + ',\'draw\',' + draw + ')" style="margin-right:6px">X (' + draw.toFixed(2) + ')</button>' +
        '<button class="btn btn-sm outcome-btn" data-odds="' + team2 + '" onclick="selectOutcome(' + eventId + ',\'team2_win\',' + team2 + ')">2 (' + team2.toFixed(2) + ')</button>' +
        '</div>' +
        '<div class="form-group" style="display:flex;gap:10px;align-items:flex-end">' +
        '<div style="flex:1">' +
        '<label for="betAmount-' + eventId + '" style="display:block;margin-bottom:5px">Amount (1-' + window.userMaxBet + ') <small style="font-weight:400;color:#888">Depends on user default currency</small></label>' +
        '<input type="number" id="betAmount-' + eventId + '" min="1" max="' + window.userMaxBet + '" step="0.01" style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px">' +
        '</div>' +
        '<button class="btn btn-primary" onclick="placeInlineBet(' + eventId + ',\'' + eventName.replace(/'/g, "\\'") + '\')">Place Bet</button>' +
        '</div>' +
        '<div id="betResult-' + eventId + '" style="margin-top:8px"></div>' +
        '</td>' +
        '</tr>';
    $('#eventRow-' + eventId).after(html);
}

function removeInlineBet(eventId) {
    $('#inlineBet-' + eventId).remove();
}

function selectOutcome(eventId, outcome, odds) {
    selectedOutcome[eventId] = { outcome: outcome, odds: odds };
    $('.outcome-btn').removeClass('active');
    $('#inlineBet-' + eventId + ' .outcome-btn').each(function() {
        if ($(this).data('odds') == odds) {
            $(this).addClass('active');
        }
    });
}

function placeInlineBet(eventId, eventName) {
    var sel = selectedOutcome[eventId];
    if (!sel) {
        $('#betResult-' + eventId).html('<span class="error">Please select an outcome</span>');
        return;
    }
    var amount = parseFloat($('#betAmount-' + eventId).val());
    var maxBet = window.userMaxBet || 500;
    if (!amount || amount < 1 || amount > maxBet) {
        $('#betResult-' + eventId).html('<span class="error">Amount must be between 1 and ' + maxBet + '</span>');
        return;
    }

    $('#betResult-' + eventId).html('');

    $.post('/api/placeBet', {
        eventName: eventName,
        outcome: sel.outcome,
        odds: sel.odds,
        amount: amount
    }, function(response) {
        if (response.success) {
            removeInlineBet(eventId);
            loadEvents();
            loadBalance();
            loadMyBets();
            loadLogs();
        }
    }).fail(function(xhr) {
        var err = xhr.responseJSON ? xhr.responseJSON.error : 'Failed to place bet';
        $('#betResult-' + eventId).html('<span class="alert alert-error" style="display:inline-block;margin-top:0">' + err + '</span>');
    });
}

function loadMyBets() {
    $.get('/api/myBets', function(bets) {
        if (!bets.length) {
            $('#myBetsContainer').html('<p>No bets yet.</p>');
            return;
        }
        var html = '<table class="bets-table"><thead><tr>' +
            '<th>Event</th><th>Outcome</th><th>Odds</th><th>Amount</th><th>Status</th><th>Date</th>' +
            '</tr></thead><tbody>';
        bets.forEach(function(b) {
            var outcomeLabels = { team1_win: '1', draw: 'X', team2_win: '2' };
            var statusClass = b.status === 'won' ? 'status-won' : (b.status === 'lost' ? 'status-lost' : 'status-pending');
            html += '<tr>' +
                '<td>' + b.event_name + '</td>' +
                '<td>' + (outcomeLabels[b.outcome] || b.outcome) + '</td>' +
                '<td>' + parseFloat(b.odds).toFixed(2) + '</td>' +
                '<td>' + parseFloat(b.amount).toFixed(2) + ' ' + b.currency + '</td>' +
                '<td class="' + statusClass + '">' + b.status + '</td>' +
                '<td>' + b.created_at + '</td>' +
                '</tr>';
        });
        html += '</tbody></table>';
        $('#myBetsContainer').html(html);
    });
}

function loadLogs() {
    $.get('/api/logs', function(logs) {
        if (!logs.length) {
            $('#logsContainer').html('<p>No balance changes recorded yet.</p>');
            return;
        }
        var html = '<div style="overflow-x:auto"><table class="data-table"><thead><tr>' +
            '<th>Action</th><th>Currency</th><th>Change</th><th>Balance</th><th>Note</th><th>Date</th>' +
            '</tr></thead><tbody>';
        logs.forEach(function(l) {
            var changeClass = parseFloat(l.amount) < 0 ? 'status-lost' : (parseFloat(l.amount) > 0 ? 'status-won' : '');
            html += '<tr>' +
                '<td>' + l.action + '</td>' +
                '<td>' + l.currency + '</td>' +
                '<td class="' + changeClass + '">' + parseFloat(l.amount).toFixed(2) + '</td>' +
                '<td>' + parseFloat(l.balance_after).toFixed(2) + '</td>' +
                '<td>' + (l.note || '') + '</td>' +
                '<td>' + l.created_at + '</td>' +
                '</tr>';
        });
        html += '</tbody></table></div>';
        $('#logsContainer').html(html);
    });
}

function logout() {
    $.post('/login/logout', {}, function() {
        window.location.href = '/login';
    });
}
</script>
