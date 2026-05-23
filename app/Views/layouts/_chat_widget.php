<!-- ── Messages Widget ── -->
<div id="chat-widget" class="msng-window d-none">

    <!-- Sidebar: contact list -->
    <div class="msng-sidebar" id="msng-sidebar">
        <div class="msng-sidebar-header">
            <span class="msng-sidebar-title">Messages</span>
            <button class="msng-close-btn" onclick="closeChat()" title="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="msng-contact-list" id="msng-contact-list">
            <div class="msng-contact active" data-id="clinic" data-name="Clinic Support" data-icon="bi-hospital" data-color="#3b82f6" onclick="selectContact(this)">
                <div class="msng-avatar" style="background:#eff6ff;color:#3b82f6;"><i class="bi bi-hospital"></i></div>
                <div class="msng-contact-info">
                    <div class="msng-contact-name">Clinic Support</div>
                    <div class="msng-contact-preview" id="preview-clinic">General inquiries</div>
                </div>
            </div>
            <div class="msng-contact" data-id="dr-santos" data-name="Dr. Santos" data-icon="bi-person-fill" data-color="#10b981" onclick="selectContact(this)">
                <div class="msng-avatar" style="background:#f0fdf4;color:#10b981;"><i class="bi bi-person-fill"></i></div>
                <div class="msng-contact-info">
                    <div class="msng-contact-name">Dr. Santos</div>
                    <div class="msng-contact-preview" id="preview-dr-santos">General Practitioner</div>
                </div>
            </div>
            <div class="msng-contact" data-id="dr-reyes" data-name="Dr. Reyes" data-icon="bi-person-fill" data-color="#8b5cf6" onclick="selectContact(this)">
                <div class="msng-avatar" style="background:#f5f3ff;color:#8b5cf6;"><i class="bi bi-person-fill"></i></div>
                <div class="msng-contact-info">
                    <div class="msng-contact-name">Dr. Reyes</div>
                    <div class="msng-contact-preview" id="preview-dr-reyes">Cardiologist</div>
                </div>
            </div>
            <div class="msng-contact" data-id="dr-cruz" data-name="Dr. Cruz" data-icon="bi-person-fill" data-color="#f59e0b" onclick="selectContact(this)">
                <div class="msng-avatar" style="background:#fffbeb;color:#f59e0b;"><i class="bi bi-person-fill"></i></div>
                <div class="msng-contact-info">
                    <div class="msng-contact-name">Dr. Cruz</div>
                    <div class="msng-contact-preview" id="preview-dr-cruz">Pediatrician</div>
                </div>
            </div>
            <div class="msng-contact" data-id="dr-garcia" data-name="Dr. Garcia" data-icon="bi-person-fill" data-color="#ef4444" onclick="selectContact(this)">
                <div class="msng-avatar" style="background:#fff1f2;color:#ef4444;"><i class="bi bi-person-fill"></i></div>
                <div class="msng-contact-info">
                    <div class="msng-contact-name">Dr. Garcia</div>
                    <div class="msng-contact-preview" id="preview-dr-garcia">Dermatologist</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conversation pane -->
    <div class="msng-convo" id="msng-convo">
        <div class="msng-convo-header">
            <button class="msng-back-btn d-none" id="msng-back-btn" onclick="showSidebar()"><i class="bi bi-arrow-left"></i></button>
            <div class="msng-avatar msng-avatar-sm" id="msng-convo-avatar" style="background:#eff6ff;color:#3b82f6;"><i class="bi bi-hospital"></i></div>
            <div>
                <div class="msng-convo-name" id="msng-convo-name">Clinic Support</div>
                <div class="msng-convo-status"><span class="msng-dot"></span> Active</div>
            </div>
        </div>
        <div class="msng-messages" id="chat-messages"></div>
        <div class="msng-input-row">
            <input type="text" id="chat-input" class="msng-input" placeholder="Aa" onkeydown="if(event.key==='Enter') sendMessage()">
            <button class="msng-send-btn" onclick="sendMessage()"><i class="bi bi-send-fill"></i></button>
        </div>
    </div>
</div>

<!-- Floating Chat Button -->
<button class="chat-fab" id="chat-fab" onclick="openChat()" title="Messages">
    <i class="bi bi-chat-dots-fill"></i>
    <span class="chat-fab-dot d-none" id="chat-fab-dot"></span>
</button>

<script>
(function () {
    const CHAT_KEY = 'oabsc_chat_messages';
    let currentContact = { id: 'clinic', name: 'Clinic Support', icon: 'bi-hospital', color: '#3b82f6' };

    function getMessages() {
        try { return JSON.parse(localStorage.getItem(CHAT_KEY) || '{}'); } catch(e) { return {}; }
    }
    function saveMessages(data) { localStorage.setItem(CHAT_KEY, JSON.stringify(data)); }

    function getContactMessages() {
        return (getMessages()[currentContact.id] || []);
    }
    function addMessage(msg) {
        const all = getMessages();
        if (!all[currentContact.id]) all[currentContact.id] = [];
        all[currentContact.id].push(msg);
        saveMessages(all);
        updatePreview(currentContact.id, msg.text);
    }

    function updatePreview(id, text) {
        const el = document.getElementById('preview-' + id);
        if (el) el.textContent = text.length > 28 ? text.slice(0, 28) + '...' : text;
    }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function nowTime() {
        return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function renderMessages() {
        const container = document.getElementById('chat-messages');
        const msgs = getContactMessages();
        if (msgs.length === 0) {
            container.innerHTML = '<div class="msng-empty"><i class="bi bi-chat-left"></i><div>No messages yet</div></div>';
            return;
        }
        let html = '';
        msgs.forEach(function(m, i) {
            const isMe = m.from === 'me';
            const showAvatar = !isMe && (i === msgs.length - 1 || msgs[i+1].from === 'me');
            html += '<div class="msng-row ' + (isMe ? 'msng-row-me' : 'msng-row-them') + '">';
            if (!isMe) {
                html += showAvatar
                    ? '<div class="msng-bubble-avatar" style="background:#f1f5f9;color:' + currentContact.color + ';"><i class="bi ' + currentContact.icon + '"></i></div>'
                    : '<div class="msng-bubble-avatar-spacer"></div>';
            }
            html += '<div class="msng-bubble-wrap">';
            html += '<div class="msng-bubble ' + (isMe ? 'msng-bubble-me' : 'msng-bubble-them') + '">' + escHtml(m.text) + '</div>';
            if (isMe && i === msgs.length - 1) {
                html += '<div class="msng-sent"><i class="bi bi-check2"></i> Sent ' + m.time + '</div>';
            }
            html += '</div></div>';
        });
        container.innerHTML = html;
        container.scrollTop = container.scrollHeight;
    }

    window.sendMessage = function () {
        const input = document.getElementById('chat-input');
        const text = input.value.trim();
        if (!text) return;
        addMessage({ from: 'me', text: text, time: nowTime() });
        input.value = '';
        renderMessages();
    };

    window.openChat = function () {
        document.getElementById('chat-widget').classList.remove('d-none');
        document.getElementById('chat-fab').classList.add('d-none');
        document.getElementById('chat-fab-dot').classList.add('d-none');
        renderMessages();
        setTimeout(function(){ document.getElementById('chat-input').focus(); }, 100);
    };

    window.closeChat = function () {
        document.getElementById('chat-widget').classList.add('d-none');
        document.getElementById('chat-fab').classList.remove('d-none');
    };

    window.selectContact = function (el) {
        currentContact = {
            id:    el.dataset.id,
            name:  el.dataset.name,
            icon:  el.dataset.icon,
            color: el.dataset.color
        };
        document.querySelectorAll('.msng-contact').forEach(function(c){ c.classList.remove('active'); });
        el.classList.add('active');

        document.getElementById('msng-convo-name').textContent = currentContact.name;
        var av = document.getElementById('msng-convo-avatar');
        av.style.background = currentContact.color + '22';
        av.style.color = currentContact.color;
        av.querySelector('i').className = 'bi ' + currentContact.icon;

        if (window.innerWidth < 540) {
            document.getElementById('msng-sidebar').style.display = 'none';
            document.getElementById('msng-convo').style.display = 'flex';
            document.getElementById('msng-back-btn').classList.remove('d-none');
        }
        renderMessages();
        setTimeout(function(){ document.getElementById('chat-input').focus(); }, 100);
    };

    window.showSidebar = function () {
        document.getElementById('msng-sidebar').style.display = '';
        document.getElementById('msng-convo').style.display = '';
        document.getElementById('msng-back-btn').classList.add('d-none');
    };
})();
</script>

<style>
    .chat-fab {
        position: fixed; bottom: 28px; right: 28px;
        width: 54px; height: 54px; border-radius: 50%;
        background: linear-gradient(135deg, #6d28d9, #4f46e5);
        color: white; border: none; font-size: 1.3rem;
        box-shadow: 0 6px 20px rgba(109,40,217,0.4);
        cursor: pointer; z-index: 1050;
        display: flex; align-items: center; justify-content: center;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .chat-fab:hover { transform: scale(1.08); }
    .chat-fab-dot {
        position: absolute; top: 6px; right: 6px;
        width: 10px; height: 10px; border-radius: 50%;
        background: #ef4444; border: 2px solid white;
    }
    .msng-window {
        position: fixed; bottom: 28px; right: 28px;
        width: 620px; height: 500px;
        background: white; border-radius: 16px;
        box-shadow: 0 16px 48px rgba(15,23,42,0.2);
        border: 1px solid #e2e8f0;
        display: flex; flex-direction: row;
        z-index: 1050; overflow: hidden;
    }
    .msng-sidebar {
        width: 220px; flex-shrink: 0;
        border-right: 1px solid #f1f5f9;
        display: flex; flex-direction: column;
        background: #fafbfc;
    }
    .msng-sidebar-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 14px 10px; border-bottom: 1px solid #f1f5f9;
    }
    .msng-sidebar-title { font-size: 0.95rem; font-weight: 700; color: #0f172a; }
    .msng-close-btn {
        background: none; border: none; color: #94a3b8;
        font-size: 0.85rem; cursor: pointer; padding: 4px 6px; border-radius: 6px;
        transition: background 0.15s;
    }
    .msng-close-btn:hover { background: #f1f5f9; color: #0f172a; }
    .msng-contact-list { flex: 1; overflow-y: auto; }
    .msng-contact {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 14px; cursor: pointer; transition: background 0.15s;
    }
    .msng-contact:hover { background: #f1f5f9; }
    .msng-contact.active { background: #ede9fe; }
    .msng-avatar {
        width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1rem;
    }
    .msng-avatar-sm { width: 32px; height: 32px; font-size: 0.85rem; }
    .msng-contact-info { min-width: 0; }
    .msng-contact-name { font-size: 0.82rem; font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .msng-contact-preview { font-size: 0.72rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .msng-convo { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    .msng-convo-header {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 16px; border-bottom: 1px solid #f1f5f9;
        background: white; flex-shrink: 0;
    }
    .msng-back-btn { background: none; border: none; color: #6d28d9; font-size: 1rem; cursor: pointer; padding: 4px 6px; }
    .msng-convo-name { font-size: 0.88rem; font-weight: 700; color: #0f172a; }
    .msng-convo-status { font-size: 0.7rem; color: #10b981; display: flex; align-items: center; gap: 4px; }
    .msng-dot { width: 6px; height: 6px; border-radius: 50%; background: #10b981; display: inline-block; }
    .msng-messages {
        flex: 1; overflow-y: auto; padding: 14px 16px;
        display: flex; flex-direction: column; gap: 3px;
        background: #f8fafc;
    }
    .msng-empty { text-align: center; color: #cbd5e1; font-size: 0.82rem; margin: auto; }
    .msng-empty i { font-size: 2rem; display: block; margin-bottom: 6px; }
    .msng-row { display: flex; align-items: flex-end; gap: 6px; margin-bottom: 2px; }
    .msng-row-me { flex-direction: row-reverse; }
    .msng-row-them { flex-direction: row; }
    .msng-bubble-avatar {
        width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 0.72rem;
    }
    .msng-bubble-avatar-spacer { width: 26px; flex-shrink: 0; }
    .msng-bubble-wrap { display: flex; flex-direction: column; max-width: 65%; }
    .msng-bubble {
        padding: 8px 13px; border-radius: 18px;
        font-size: 0.83rem; line-height: 1.45; word-break: break-word;
        display: inline-block;
    }
    .msng-bubble-me { background: #6d28d9; color: white; border-bottom-right-radius: 4px; align-self: flex-end; }
    .msng-bubble-them { background: #e9ecef; color: #0f172a; border-bottom-left-radius: 4px; align-self: flex-start; }
    .msng-sent { font-size: 0.65rem; color: #94a3b8; text-align: right; margin-top: 2px; padding-right: 2px; }
    .msng-input-row {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 14px; border-top: 1px solid #f1f5f9;
        background: white; flex-shrink: 0;
    }
    .msng-input {
        flex: 1; border: 1px solid #e2e8f0; border-radius: 20px;
        padding: 8px 14px; font-size: 0.83rem; outline: none;
        background: #f8fafc; transition: border-color 0.15s;
    }
    .msng-input:focus { border-color: #6d28d9; background: white; }
    .msng-send-btn {
        background: #6d28d9; color: white; border: none;
        border-radius: 50%; width: 36px; height: 36px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.85rem; cursor: pointer; transition: background 0.15s;
    }
    .msng-send-btn:hover { background: #5b21b6; }
    @media (max-width: 540px) {
        .msng-window { width: calc(100vw - 16px); height: 90vh; bottom: 8px; right: 8px; border-radius: 14px; }
        .msng-sidebar { width: 100%; border-right: none; }
        .msng-convo { display: none; }
        .chat-fab { right: 16px; bottom: 16px; }
    }
</style>
