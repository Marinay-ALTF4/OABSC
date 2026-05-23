$content = Get-Content 'app/Views/auth/dashboard.php' -Raw -Encoding UTF8

$startMarker = '<!-- ── Chat Widget ── -->'
$endMarker = '@media (max-width: 480px) {' + "`r`n" + '        .chat-widget { width: calc(100vw - 24px); right: 12px; bottom: 12px; }' + "`r`n" + '        .chat-fab    { right: 16px; bottom: 16px; }' + "`r`n" + '    }' + "`r`n" + '</style>'

$startIdx = $content.IndexOf($startMarker)
$endIdx   = $content.IndexOf($endMarker)
$endFull  = $endIdx + $endMarker.Length

$before = $content.Substring(0, $startIdx)
$after  = $content.Substring($endFull)

$newChat = @'
<!-- ── Messenger Chat Widget ── -->
<div id="chat-widget" class="msng-widget d-none">

    <!-- Header -->
    <div class="msng-header">
        <button class="msng-back-btn d-none" id="msng-back-btn" onclick="showContactList()" title="Back"><i class="bi bi-arrow-left"></i></button>
        <div class="msng-header-info" id="msng-header-info">
            <div class="msng-header-avatar" id="msng-header-avatar" style="background:#eff6ff;color:#3b82f6;">
                <i class="bi bi-hospital"></i>
            </div>
            <div>
                <div class="msng-header-name" id="msng-header-name">Messages</div>
                <div class="msng-header-status" id="msng-header-status"></div>
            </div>
        </div>
        <button class="msng-close-btn" onclick="closeChat()" title="Close"><i class="bi bi-x-lg"></i></button>
    </div>

    <!-- Contact List View -->
    <div id="msng-contacts-view" class="msng-contacts-view">
        <div class="msng-search-wrap">
            <i class="bi bi-search msng-search-icon"></i>
            <input type="text" class="msng-search" id="msng-search" placeholder="Search contacts..." oninput="filterContacts(this.value)">
        </div>
        <div class="msng-contact-list" id="msng-contact-list">
            <div class="msng-contact-item" data-id="clinic" data-name="Clinic Support" data-icon="bi-hospital" data-color="#3b82f6" onclick="openConversation(this)">
                <div class="msng-contact-avatar" style="background:#eff6ff;color:#3b82f6;"><i class="bi bi-hospital"></i></div>
                <div class="msng-contact-body">
                    <div class="msng-contact-name">Clinic Support</div>
                    <div class="msng-contact-preview" id="preview-clinic">General inquiries</div>
                </div>
                <div class="msng-contact-meta">
                    <div class="msng-contact-time" id="time-clinic"></div>
                    <div class="msng-unread-badge d-none" id="badge-clinic">0</div>
                </div>
            </div>
            <div class="msng-contact-item" data-id="dr-santos" data-name="Dr. Santos" data-icon="bi-person-fill" data-color="#10b981" onclick="openConversation(this)">
                <div class="msng-contact-avatar" style="background:#f0fdf4;color:#10b981;"><i class="bi bi-person-fill"></i></div>
                <div class="msng-contact-body">
                    <div class="msng-contact-name">Dr. Santos</div>
                    <div class="msng-contact-preview" id="preview-dr-santos">General Practitioner</div>
                </div>
                <div class="msng-contact-meta">
                    <div class="msng-contact-time" id="time-dr-santos"></div>
                    <div class="msng-unread-badge d-none" id="badge-dr-santos">0</div>
                </div>
            </div>
            <div class="msng-contact-item" data-id="dr-reyes" data-name="Dr. Reyes" data-icon="bi-person-fill" data-color="#8b5cf6" onclick="openConversation(this)">
                <div class="msng-contact-avatar" style="background:#f5f3ff;color:#8b5cf6;"><i class="bi bi-person-fill"></i></div>
                <div class="msng-contact-body">
                    <div class="msng-contact-name">Dr. Reyes</div>
                    <div class="msng-contact-preview" id="preview-dr-reyes">Cardiologist</div>
                </div>
                <div class="msng-contact-meta">
                    <div class="msng-contact-time" id="time-dr-reyes"></div>
                    <div class="msng-unread-badge d-none" id="badge-dr-reyes">0</div>
                </div>
            </div>
            <div class="msng-contact-item" data-id="dr-cruz" data-name="Dr. Cruz" data-icon="bi-person-fill" data-color="#f59e0b" onclick="openConversation(this)">
                <div class="msng-contact-avatar" style="background:#fffbeb;color:#f59e0b;"><i class="bi bi-person-fill"></i></div>
                <div class="msng-contact-body">
                    <div class="msng-contact-name">Dr. Cruz</div>
                    <div class="msng-contact-preview" id="preview-dr-cruz">Pediatrician</div>
                </div>
                <div class="msng-contact-meta">
                    <div class="msng-contact-time" id="time-dr-cruz"></div>
                    <div class="msng-unread-badge d-none" id="badge-dr-cruz">0</div>
                </div>
            </div>
            <div class="msng-contact-item" data-id="dr-garcia" data-name="Dr. Garcia" data-icon="bi-person-fill" data-color="#ef4444" onclick="openConversation(this)">
                <div class="msng-contact-avatar" style="background:#fff1f2;color:#ef4444;"><i class="bi bi-person-fill"></i></div>
                <div class="msng-contact-body">
                    <div class="msng-contact-name">Dr. Garcia</div>
                    <div class="msng-contact-preview" id="preview-dr-garcia">Dermatologist</div>
                </div>
                <div class="msng-contact-meta">
                    <div class="msng-contact-time" id="time-dr-garcia"></div>
                    <div class="msng-unread-badge d-none" id="badge-dr-garcia">0</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conversation View -->
    <div id="msng-convo-view" class="msng-convo-view d-none">
        <div id="msng-messages" class="msng-messages"></div>
        <div class="msng-input-bar">
            <button class="msng-attach-btn" title="Attach"><i class="bi bi-plus-circle"></i></button>
            <input type="text" id="chat-input" class="msng-input" placeholder="Aa" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage()}" oninput="onTyping()">
            <button class="msng-send-btn" id="msng-send-btn" onclick="sendMessage()"><i class="bi bi-send-fill"></i></button>
        </div>
    </div>
</div>

<!-- Floating Chat Button -->
<button class="msng-fab" id="chat-fab" onclick="openChat()" title="Messages">
    <i class="bi bi-messenger"></i>
    <span class="msng-fab-badge d-none" id="chat-fab-dot">1</span>
</button>

<script>
(function () {
    const CHAT_KEY = 'oabsc_chat_v2';
    let currentContact = null;

    function getData() {
        try { return JSON.parse(localStorage.getItem(CHAT_KEY) || '{}'); } catch(e) { return {}; }
    }
    function saveData(d) { localStorage.setItem(CHAT_KEY, JSON.stringify(d)); }

    function getMsgs(id) {
        return (getData()[id] || {}).messages || [];
    }
    function pushMsg(id, msg) {
        const d = getData();
        if (!d[id]) d[id] = { messages: [] };
        d[id].messages.push(msg);
        saveData(d);
    }

    function nowTime() {
        return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    /* ── Render conversation ── */
    function renderConvo() {
        const container = document.getElementById('msng-messages');
        const msgs = getMsgs(currentContact.id);

        if (!msgs.length) {
            container.innerHTML = `<div class="msng-empty">
                <div class="msng-empty-avatar" style="background:${currentContact.color}22;color:${currentContact.color};">
                    <i class="bi ${currentContact.icon}" style="font-size:1.6rem;"></i>
                </div>
                <div class="msng-empty-name">${escHtml(currentContact.name)}</div>
                <div class="msng-empty-hint">Send a message to start the conversation.</div>
            </div>`;
            return;
        }

        let html = '';
        let prevFrom = null;
        msgs.forEach((m, i) => {
            const isMe = m.from === 'me';
            const sameAsPrev = prevFrom === m.from;
            const isLast = i === msgs.length - 1 || msgs[i+1].from !== m.from;

            const bubbleClass = isMe ? 'msng-bubble-out' : 'msng-bubble-in';
            const groupClass  = sameAsPrev ? 'msng-group-cont' : 'msng-group-start';
            const tailClass   = isLast ? 'msng-tail' : '';

            const avatar = (!isMe && isLast)
                ? `<div class="msng-avatar-xs" style="background:${currentContact.color}22;color:${currentContact.color};"><i class="bi ${currentContact.icon}"></i></div>`
                : (!isMe ? '<div class="msng-avatar-xs msng-avatar-spacer"></div>' : '');

            html += `<div class="msng-row ${isMe ? 'msng-row-out' : 'msng-row-in'} ${groupClass}">
                ${avatar}
                <div class="msng-bubble ${bubbleClass} ${tailClass}">${escHtml(m.text)}</div>
            </div>`;

            if (isLast) {
                html += `<div class="msng-timestamp ${isMe ? 'msng-ts-right' : 'msng-ts-left'}">${m.time}${isMe && isLast ? ' <span class="msng-seen"><i class="bi bi-check2-all"></i></span>' : ''}</div>`;
            }

            prevFrom = m.from;
        });

        container.innerHTML = html;
        container.scrollTop = container.scrollHeight;
    }

    /* ── Update contact list previews ── */
    function refreshPreviews() {
        document.querySelectorAll('.msng-contact-item').forEach(el => {
            const id = el.dataset.id;
            const msgs = getMsgs(id);
            const previewEl = document.getElementById('preview-' + id);
            const timeEl    = document.getElementById('time-' + id);
            if (msgs.length && previewEl) {
                const last = msgs[msgs.length - 1];
                previewEl.textContent = (last.from === 'me' ? 'You: ' : '') + last.text.substring(0, 32) + (last.text.length > 32 ? '…' : '');
                if (timeEl) timeEl.textContent = last.time;
            }
        });
    }

    /* ── Send message ── */
    window.sendMessage = function () {
        const input = document.getElementById('chat-input');
        const text  = input.value.trim();
        if (!text || !currentContact) return;

        pushMsg(currentContact.id, { from: 'me', text, time: nowTime() });
        input.value = '';
        toggleSendBtn('');
        renderConvo();
        refreshPreviews();
    };

    window.onTyping = function () {
        toggleSendBtn(document.getElementById('chat-input').value);
    };

    function toggleSendBtn(val) {
        const btn = document.getElementById('msng-send-btn');
        if (val.trim()) {
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
        } else {
            btn.style.opacity = '0.4';
            btn.style.pointerEvents = 'none';
        }
    }

    /* ── Navigation ── */
    window.openConversation = function (el) {
        currentContact = {
            id:    el.dataset.id,
            name:  el.dataset.name,
            icon:  el.dataset.icon,
            color: el.dataset.color
        };

        // Update header
        document.getElementById('msng-header-name').textContent = currentContact.name;
        document.getElementById('msng-header-status').textContent = 'Active now';
        const av = document.getElementById('msng-header-avatar');
        av.style.background = currentContact.color + '22';
        av.style.color = currentContact.color;
        av.innerHTML = `<i class="bi ${currentContact.icon}"></i>`;

        document.getElementById('msng-back-btn').classList.remove('d-none');
        document.getElementById('msng-contacts-view').classList.add('d-none');
        document.getElementById('msng-convo-view').classList.remove('d-none');

        toggleSendBtn('');
        renderConvo();
        setTimeout(() => document.getElementById('chat-input').focus(), 80);
    };

    window.showContactList = function () {
        currentContact = null;
        document.getElementById('msng-back-btn').classList.add('d-none');
        document.getElementById('msng-header-name').textContent = 'Messages';
        document.getElementById('msng-header-status').textContent = '';
        document.getElementById('msng-header-avatar').innerHTML = '<i class="bi bi-chat-dots-fill"></i>';
        document.getElementById('msng-header-avatar').style.background = '#eff6ff';
        document.getElementById('msng-header-avatar').style.color = '#3b82f6';
        document.getElementById('msng-convo-view').classList.add('d-none');
        document.getElementById('msng-contacts-view').classList.remove('d-none');
        refreshPreviews();
    };

    window.filterContacts = function (q) {
        const lower = q.toLowerCase();
        document.querySelectorAll('.msng-contact-item').forEach(el => {
            el.style.display = el.dataset.name.toLowerCase().includes(lower) ? '' : 'none';
        });
    };

    window.openChat = function () {
        document.getElementById('chat-widget').classList.remove('d-none');
        document.getElementById('chat-fab').classList.add('d-none');
        document.getElementById('chat-fab-dot').classList.add('d-none');
        showContactList();
    };

    window.closeChat = function () {
        document.getElementById('chat-widget').classList.add('d-none');
        document.getElementById('chat-fab').classList.remove('d-none');
        currentContact = null;
    };

    // Init previews on load
    refreshPreviews();
})();
</script>

<style>
    /* ── FAB ── */
    .msng-fab {
        position: fixed; bottom: 28px; right: 28px;
        width: 56px; height: 56px; border-radius: 50%;
        background: linear-gradient(135deg, #0084ff, #0063cc);
        color: white; border: none; font-size: 1.4rem;
        box-shadow: 0 4px 18px rgba(0,132,255,0.45);
        cursor: pointer; z-index: 1050;
        display: flex; align-items: center; justify-content: center;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .msng-fab:hover { transform: scale(1.08); box-shadow: 0 6px 24px rgba(0,132,255,0.55); }
    .msng-fab-badge {
        position: absolute; top: 4px; right: 4px;
        min-width: 18px; height: 18px; border-radius: 9px;
        background: #ef4444; color: white; font-size: 0.65rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        border: 2px solid white; padding: 0 3px;
    }

    /* ── Widget shell ── */
    .msng-widget {
        position: fixed; bottom: 28px; right: 28px;
        width: 360px; height: 560px;
        background: #fff; border-radius: 16px;
        box-shadow: 0 8px 40px rgba(0,0,0,0.18);
        display: flex; flex-direction: column;
        z-index: 1050; overflow: hidden;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    /* ── Header ── */
    .msng-header {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 14px;
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        flex-shrink: 0;
    }
    .msng-back-btn, .msng-close-btn {
        background: none; border: none; cursor: pointer;
        color: #0084ff; font-size: 1rem;
        width: 32px; height: 32px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.15s; flex-shrink: 0;
    }
    .msng-back-btn:hover, .msng-close-btn:hover { background: #f0f2f5; }
    .msng-close-btn { color: #65676b; margin-left: auto; }
    .msng-header-info { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; }
    .msng-header-avatar {
        width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1rem;
    }
    .msng-header-name { font-size: 0.9rem; font-weight: 700; color: #050505; line-height: 1.2; }
    .msng-header-status { font-size: 0.72rem; color: #65676b; }

    /* ── Contact list ── */
    .msng-contacts-view { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .msng-search-wrap {
        position: relative; padding: 10px 14px 8px;
        flex-shrink: 0;
    }
    .msng-search-icon { position: absolute; left: 24px; top: 50%; transform: translateY(-50%); color: #65676b; font-size: 0.8rem; pointer-events: none; }
    .msng-search {
        width: 100%; background: #f0f2f5; border: none; border-radius: 20px;
        padding: 7px 14px 7px 32px; font-size: 0.83rem; outline: none; color: #050505;
    }
    .msng-contact-list { flex: 1; overflow-y: auto; }
    .msng-contact-list::-webkit-scrollbar { width: 4px; }
    .msng-contact-list::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }
    .msng-contact-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 14px; cursor: pointer; transition: background 0.12s;
    }
    .msng-contact-item:hover { background: #f0f2f5; }
    .msng-contact-avatar {
        width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    }
    .msng-contact-body { flex: 1; min-width: 0; }
    .msng-contact-name { font-size: 0.88rem; font-weight: 600; color: #050505; }
    .msng-contact-preview { font-size: 0.78rem; color: #65676b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px; }
    .msng-contact-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0; }
    .msng-contact-time { font-size: 0.7rem; color: #65676b; }
    .msng-unread-badge {
        min-width: 18px; height: 18px; border-radius: 9px;
        background: #0084ff; color: white; font-size: 0.65rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center; padding: 0 4px;
    }

    /* ── Conversation ── */
    .msng-convo-view { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .msng-messages {
        flex: 1; overflow-y: auto; padding: 12px 14px;
        display: flex; flex-direction: column; gap: 2px;
    }
    .msng-messages::-webkit-scrollbar { width: 4px; }
    .msng-messages::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }

    /* Empty state */
    .msng-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 1; padding: 2rem; text-align: center; margin: auto; }
    .msng-empty-avatar { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
    .msng-empty-name { font-size: 0.95rem; font-weight: 700; color: #050505; margin-bottom: 6px; }
    .msng-empty-hint { font-size: 0.78rem; color: #65676b; }

    /* Rows */
    .msng-row { display: flex; align-items: flex-end; gap: 6px; max-width: 85%; }
    .msng-row-out { align-self: flex-end; flex-direction: row-reverse; }
    .msng-row-in  { align-self: flex-start; }
    .msng-group-start { margin-top: 10px; }
    .msng-group-cont  { margin-top: 2px; }

    /* Bubbles */
    .msng-bubble {
        padding: 9px 13px; border-radius: 18px;
        font-size: 0.875rem; line-height: 1.45; word-break: break-word;
        max-width: 100%;
    }
    .msng-bubble-out {
        background: #0084ff; color: #fff;
        border-bottom-right-radius: 4px;
    }
    .msng-bubble-in {
        background: #f0f2f5; color: #050505;
        border-bottom-left-radius: 4px;
    }
    .msng-bubble-out.msng-tail { border-bottom-right-radius: 18px; }
    .msng-bubble-in.msng-tail  { border-bottom-left-radius: 18px; }

    /* Avatar next to incoming */
    .msng-avatar-xs {
        width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 0.75rem;
    }
    .msng-avatar-spacer { visibility: hidden; }

    /* Timestamps */
    .msng-timestamp { font-size: 0.68rem; color: #65676b; margin: 3px 0 6px; }
    .msng-ts-right { text-align: right; padding-right: 4px; }
    .msng-ts-left  { text-align: left;  padding-left: 32px; }
    .msng-seen { color: #0084ff; }

    /* Input bar */
    .msng-input-bar {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 12px; border-top: 1px solid #f0f0f0; flex-shrink: 0;
    }
    .msng-attach-btn {
        background: none; border: none; color: #0084ff; font-size: 1.3rem;
        cursor: pointer; padding: 0; line-height: 1; flex-shrink: 0;
        transition: opacity 0.15s;
    }
    .msng-attach-btn:hover { opacity: 0.75; }
    .msng-input {
        flex: 1; background: #f0f2f5; border: none; border-radius: 20px;
        padding: 9px 14px; font-size: 0.875rem; outline: none; color: #050505;
        resize: none; line-height: 1.4;
    }
    .msng-send-btn {
        background: none; border: none; color: #0084ff; font-size: 1.15rem;
        cursor: pointer; padding: 0; line-height: 1; flex-shrink: 0;
        opacity: 0.4; pointer-events: none; transition: opacity 0.15s;
    }

    @media (max-width: 480px) {
        .msng-widget { width: calc(100vw - 16px); height: calc(100vh - 80px); right: 8px; bottom: 8px; border-radius: 12px; }
        .msng-fab { right: 16px; bottom: 16px; }
    }
</style>
'@

$newContent = $before + $newChat + $after
[System.IO.File]::WriteAllText((Resolve-Path 'app/Views/auth/dashboard.php'), $newContent, [System.Text.Encoding]::UTF8)
Write-Host "Done"
