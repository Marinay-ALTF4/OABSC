<?php
$db = \Config\Database::connect();
$session = session();
$userId = (int) $session->get('user_id');
$userRole = $session->get('user_role') ?? 'guest';
$userName = $session->get('user_name') ?? 'Guest';

$chatbotData = [];

if ($userRole === 'client') {
    // 1. Appointments for Patient
    $appts = $db->query("
        SELECT COALESCE(up.name, 'Unknown Doctor') as doctor_name, a.appointment_date, a.appointment_time, a.status 
        FROM appointments a
        LEFT JOIN user_profiles up ON up.user_id = a.doctor_id
        WHERE a.user_id = ? AND a.archived_at IS NULL
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
    ", [$userId])->getResultArray();
    $chatbotData['appointments'] = $appts;

    // 2. Prescriptions for Patient
        $prescriptions = [];
        if ($db->tableExists('prescriptions')) {
            $prescriptions = $db->query("
                SELECT medication_name, dosage, frequency, duration, instructions, created_at 
                FROM prescriptions 
                WHERE patient_id = ?
                ORDER BY created_at DESC
            ", [$userId])->getResultArray();
        }
    $chatbotData['prescriptions'] = $prescriptions;
} elseif ($userRole === 'doctor') {
    // Patients count/lookup
    $patients = $db->query("
        SELECT u.id, up.name, u.email 
        FROM users u
        LEFT JOIN user_profiles up ON up.user_id = u.id
        WHERE u.role = 'client' AND u.status = 'active' AND u.deleted_at IS NULL
    ")->getResultArray();
    $chatbotData['patients'] = $patients;
} elseif ($userRole === 'secretary') {
    // Pending count
    $pendingCount = $db->query("
        SELECT COUNT(*) as cnt 
        FROM appointments 
        WHERE status = 'pending' AND archived_at IS NULL
    ")->getRow()->cnt;
    $chatbotData['pending_count'] = (int) $pendingCount;
} elseif (in_array($userRole, ['admin', 'assistant_admin'])) {
    // Total users and active logs
    $userCount = $db->query("SELECT COUNT(*) as cnt FROM users")->getRow()->cnt;
    $eventCount = $db->query("SELECT COUNT(*) as cnt FROM login_events")->getRow()->cnt;
    $chatbotData['user_count'] = (int) $userCount;
    $chatbotData['event_count'] = (int) $eventCount;
}
?>

<!-- Chatbot Floating Window -->
<div id="chatbot-widget" class="bot-window d-none">
    <!-- Header -->
    <div class="bot-header">
        <div class="bot-header-avatar">
            <i class="bi bi-robot"></i>
            <span class="bot-online-indicator"></span>
        </div>
        <div>
            <div class="bot-header-name">Clinic Assistant</div>
            <div class="bot-header-status">Virtual Assistant (Online)</div>
        </div>
        <button class="bot-close-btn" onclick="toggleChatbot()" title="Close"><i class="bi bi-x-lg"></i></button>
    </div>

    <!-- Messages Panel -->
    <div class="bot-messages" id="bot-messages">
        <!-- Messages are rendered dynamically -->
    </div>

    <!-- Quick Options Area -->
    <div class="bot-options" id="bot-options">
        <!-- Quick action buttons rendered dynamically -->
    </div>

    <!-- Input Box -->
    <div class="bot-input-row">
        <input type="text" id="bot-input" class="bot-input" placeholder="Ask a question..."
               onkeydown="if(event.key==='Enter') sendBotMessage()">
        <button class="bot-send-btn" onclick="sendBotMessage()"><i class="bi bi-send-fill"></i></button>
    </div>
</div>

<!-- Floating Action Button for Chatbot -->
<button class="bot-fab" id="bot-fab" onclick="toggleChatbot()" title="Clinic Assistant">
    <i class="bi bi-robot"></i>
</button>

<script>
(function() {
    const userRole = '<?= $userRole ?>';
    const userName = '<?= htmlspecialchars($userName, ENT_QUOTES) ?>';
    const chatbotData = <?= json_encode($chatbotData) ?>;

    let isBotOpen = false;

    // Define options and responses per role
    const chatbotConfig = {
        client: {
            title: "Patient Support",
            greeting: "Hello, Patient " + userName + "! How can I assist you with your health services today?",
            options: [
                { label: "📋 Operating Hours", id: "hours" },
                { label: "📅 Book Appointment", id: "book" },
                { label: "🔍 Appointment Status", id: "status" },
                { label: "💊 Prescription Reminders", id: "presc" },
                { label: "🧭 Dashboard Guide", id: "guide" }
            ],
            respond: function(id, text) {
                if (id === "hours" || text.includes("hour") || text.includes("time") || text.includes("open") || text.includes("close")) {
                    return "Our clinic is open during the following hours:\n- **Monday to Friday**: 8:00 AM - 5:00 PM\n- **Saturday**: 9:00 AM - 1:00 PM\n- **Sunday**: Closed\n\nFor emergencies, please go to the nearest hospital.";
                }
                if (id === "book" || text.includes("book") || text.includes("appoint")) {
                    return "To book an appointment:\n1. Click **Appointments** in the sidebar.\n2. Click the **'New Appointment'** button.\n3. Choose your doctor, date, time, and consultation reason, then submit.";
                }
                if (id === "status" || text.includes("status")) {
                    if (!chatbotData.appointments || chatbotData.appointments.length === 0) {
                        return "You currently have no scheduled appointments. Go to the Appointments tab to book one.";
                    }
                    let res = "Here are your active appointments:\n";
                    chatbotData.appointments.forEach(a => {
                        res += `- **${a.doctor_name}**: ${a.appointment_date} at ${a.appointment_time} (${a.status})\n`;
                    });
                    return res;
                }
                if (id === "presc" || text.includes("presc") || text.includes("med") || text.includes("pill")) {
                    if (!chatbotData.prescriptions || chatbotData.prescriptions.length === 0) {
                        return "You have no active prescriptions in the system. Consult your physician for medical requirements.";
                    }
                    let res = "Here are your prescription reminders:\n";
                    chatbotData.prescriptions.forEach(p => {
                        res += `- **${p.medication_name}** (${p.dosage}): ${p.instructions}\n`;
                    });
                    return res;
                }
                if (id === "guide" || text.includes("guide") || text.includes("dashboard") || text.includes("help") || text.includes("navigate")) {
                    return "Patient Dashboard Navigation Guide:\n- **Home**: View status dashboards and notifications.\n- **Appointments**: Create, cancel, and track doctor sessions.\n- **Medical History**: View doctor notes, prescriptions, and notes.\n- **Announcements**: Monitor important clinic news.";
                }
                return null;
            }
        },
        doctor: {
            title: "Doctor Panel Support",
            greeting: "Welcome back, Dr. " + userName + ". How can I assist you with clinical record coordination today?",
            options: [
                { label: "🔍 Patient Lookup", id: "patients" },
                { label: "💊 Create Prescription", id: "presc_guide" },
                { label: "📝 Medical Notes Guide", id: "notes_guide" },
                { label: "🧭 Dashboard Guide", id: "guide" }
            ],
            respond: function(id, text) {
                if (id === "patients" || text.includes("patient") || text.includes("lookup") || text.includes("search")) {
                    if (!chatbotData.patients || chatbotData.patients.length === 0) {
                        return "No active patient records found.";
                    }
                    let res = "Here is the list of active patients:\n";
                    chatbotData.patients.forEach(p => {
                        res += `- **${p.name}** (${p.email})\n`;
                    });
                    return res + "\nTo review full histories, open the Patients panel from the sidebar.";
                }
                if (id === "presc_guide" || text.includes("presc") || text.includes("med") || text.includes("create")) {
                    return "To write a new prescription:\n1. Click **Patients** or **Medical Records** from the sidebar.\n2. Click on the patient.\n3. Scroll to the Prescription section and select **'New Prescription'**.\n4. Write dosage details and save.";
                }
                if (id === "notes_guide" || text.includes("note") || text.includes("medical")) {
                    return "To save clinical notes:\n1. Locate the patient in the **Patients** list.\n2. Click **'Add Consultation Note'**.\n3. Fill in symptoms, diagnosis, and notes, then click submit.";
                }
                if (id === "guide" || text.includes("guide") || text.includes("dashboard") || text.includes("help") || text.includes("navigate")) {
                    return "Doctor Dashboard Navigation:\n- **Dashboard**: View summary statistics and schedule overview.\n- **My Appointments**: Monitor patient consultation list.\n- **Patients**: Record prescriptions, medical notes, and inspect clinical history logs.";
                }
                return null;
            }
        },
        secretary: {
            title: "Secretary Support",
            greeting: "Hello! How can I assist you with rescheduling or dashboard tasks today?",
            options: [
                { label: "📅 Appointment Scheduling", id: "sched" },
                { label: "👥 Queue Monitor", id: "queue" },
                { label: "🧭 Dashboard Guide", id: "guide" }
            ],
            respond: function(id, text) {
                if (id === "sched" || text.includes("reschedule") || text.includes("sched") || text.includes("appointment")) {
                    return "To manage/reschedule a session:\n1. Click **Appointments** in your sidebar.\n2. Find the row and select **'Reschedule'**.\n3. Select the new date/time slots and confirm.";
                }
                if (id === "queue" || text.includes("queue") || text.includes("pending")) {
                    return "There are currently **" + (chatbotData.pending_count || 0) + "** pending appointment requests requiring review. You can approve or reject them on the Appointments page.";
                }
                if (id === "guide" || text.includes("guide") || text.includes("dashboard") || text.includes("help") || text.includes("navigate")) {
                    return "Secretary Dashboard Navigation:\n- **Appointments**: Approve, decline, and reschedule patient slots.\n- **Schedules**: Configure doctor shift calendars.";
                }
                return null;
            }
        },
        admin: {
            title: "Admin Assistant",
            greeting: "Welcome, Administrator. I am ready to assist with system and audit log management.",
            options: [
                { label: "⚙️ User Management", id: "users" },
                { label: "📊 Audit Logs & Reports", id: "reports" },
                { label: "📈 System Activity", id: "activity" },
                { label: "🧭 Dashboard Guide", id: "guide" }
            ],
            respond: function(id, text) {
                if (id === "users" || text.includes("user") || text.includes("manage") || text.includes("account")) {
                    return "To manage system users:\n1. Open **User List** from the sidebar.\n2. Select **'Add User'** to register a new profile.\n3. Click edit on any record to suspend accounts or reset keys.";
                }
                if (id === "reports" || text.includes("log") || text.includes("audit") || text.includes("report")) {
                    return "System activity data can be analyzed under:\n- **Audit Logs**: Inspect real-time operations, IP addresses, and login locations.\n- **Audit Reports**: View duration statistics and active sessions metrics.";
                }
                if (id === "activity" || text.includes("activity") || text.includes("stats")) {
                    return `System activity metrics:\n- **Total active users**: ${chatbotData.user_count || 0}\n- **Total recorded audit events**: ${chatbotData.event_count || 0}`;
                }
                if (id === "guide" || text.includes("guide") || text.includes("dashboard") || text.includes("help") || text.includes("navigate")) {
                    return "Admin Dashboard Navigation:\n- **Audit Logs / Reports**: Monitor platform safety.\n- **User Management**: Oversee client, doctor, and secretary accounts.\n- **Permissions**: Edit RBAC permissions settings.\n- **Announcements**: Send clinic-wide notices.";
                }
                return null;
            }
        },
        assistant_admin: {
            title: "Assistant Admin Support",
            greeting: "Hello, Assistant Admin! I am ready to support your database monitoring tasks.",
            options: [
                { label: "⚙️ Support Tasks Guide", id: "tasks" },
                { label: "👥 User Records Overview", id: "user_overview" },
                { label: "📊 Data Management Guide", id: "data_guide" },
                { label: "🧭 Dashboard Guide", id: "guide" }
            ],
            respond: function(id, text) {
                if (id === "tasks" || text.includes("task") || text.includes("support")) {
                    return "As Assistant Admin, your tasks include audit inspections, monitoring announcement distribution, and verifying doctor shift availability.";
                }
                if (id === "user_overview" || text.includes("user") || text.includes("record")) {
                    return `Total active registered users: **${chatbotData.user_count || 0}**. Details can be checked under the User List.`;
                }
                if (id === "data_guide" || text.includes("data") || text.includes("manage")) {
                    return "Ensure schedules are synchronized and review system access requests. Coordinate with Secretary for calendar logs.";
                }
                if (id === "guide" || text.includes("guide") || text.includes("dashboard") || text.includes("help") || text.includes("navigate")) {
                    return "Assistant Admin Navigation:\n- **User List**: Inspect credentials listings.\n- **Audit Logs**: Track system sessions.\n- **Doctor Schedules**: Confirm work calendars.";
                }
                return null;
            }
        }
    };

    const roleConfig = chatbotConfig[userRole] || null;

    // Insert bot and user message bubbles
    function addBotMessage(text, isOptionClick = false) {
        const container = document.getElementById('bot-messages');
        const bubble = document.createElement('div');
        bubble.className = 'bot-msg bot-msg-them';
        bubble.innerHTML = '<div class="bot-bubble">' + formatMarkdown(text) + '</div>';
        container.appendChild(bubble);
        container.scrollTop = container.scrollHeight;
    }

    function addUserMessage(text) {
        const container = document.getElementById('bot-messages');
        const bubble = document.createElement('div');
        bubble.className = 'bot-msg bot-msg-me';
        bubble.innerHTML = '<div class="bot-bubble">' + escHtml(text) + '</div>';
        container.appendChild(bubble);
        container.scrollTop = container.scrollHeight;
    }

    // Markdown formatter
    function formatMarkdown(text) {
        let html = escHtml(text);
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\n/g, '<br>');
        return html;
    }

    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // Load options list
    function renderOptions() {
        const wrap = document.getElementById('bot-options');
        wrap.innerHTML = '';
        if (!roleConfig) return;
        roleConfig.options.forEach(opt => {
            const btn = document.createElement('button');
            btn.className = 'bot-opt-btn';
            btn.textContent = opt.label;
            btn.onclick = () => handleOptionClick(opt);
            wrap.appendChild(btn);
        });
    }

    function handleOptionClick(opt) {
        addUserMessage(opt.label);
        const reply = roleConfig.respond(opt.id, '');
        setTimeout(() => {
            if (reply) addBotMessage(reply);
        }, 300);
    }

    window.sendBotMessage = function() {
        const input = document.getElementById('bot-input');
        const text = input.value.trim();
        if (!text || !roleConfig) return;

        addUserMessage(text);
        input.value = '';

        setTimeout(() => {
            const reply = roleConfig.respond('', text.toLowerCase());
            if (reply) {
                addBotMessage(reply);
            } else {
                addBotMessage("I didn't quite catch that. Try using one of the quick options below, or type query terms like: 'hours', 'appointment', 'guide', 'prescription', or 'logs'.");
            }
        }, 300);
    };

    window.toggleChatbot = function() {
        const botWin = document.getElementById('chatbot-widget');
        const chatWin = document.getElementById('chat-widget');
        
        isBotOpen = !isBotOpen;
        if (isBotOpen) {
            // Close peer-to-peer chat widget if open
            if (chatWin && !chatWin.classList.contains('d-none')) {
                closeChat();
            }
            botWin.classList.remove('d-none');
            document.getElementById('bot-input').focus();
        } else {
            botWin.classList.add('d-none');
        }
    };

    window.openChatbot = function() {
        const botWin = document.getElementById('chatbot-widget');
        const chatWin = document.getElementById('chat-widget');
        
        isBotOpen = true;
        if (chatWin && !chatWin.classList.contains('d-none')) {
            closeChat();
        }
        botWin.classList.remove('d-none');
        document.getElementById('bot-input').focus();
    };

    // Listen to peer-to-peer chat opening to close chatbot
    const origOpenChat = window.openChat;
    if (origOpenChat) {
        window.openChat = function() {
            const botWin = document.getElementById('chatbot-widget');
            if (botWin) {
                botWin.classList.add('d-none');
                isBotOpen = false;
            }
            origOpenChat();
        };
    }

    // Initialize chatbot UI if role is supported
    if (roleConfig) {
        document.querySelector('.bot-header-name').textContent = "Clinic Assistant (" + roleConfig.title + ")";
        addBotMessage(roleConfig.greeting);
        renderOptions();
    } else {
        // Hide chatbot FAB for guests or unsupported roles
        document.getElementById('bot-fab').style.display = 'none';
    }
})();
</script>

<style>
    /* FAB */
    .bot-fab {
        position: fixed; bottom: 28px; right: 28px;
        width: 54px; height: 54px; border-radius: 50%;
        background: linear-gradient(135deg, #0d9488, #0f766e);
        color: white; border: none; font-size: 1.3rem;
        box-shadow: 0 6px 20px rgba(13,148,136,0.35);
        cursor: pointer; z-index: 1050;
        display: flex; align-items: center; justify-content: center;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .bot-fab:hover { transform: scale(1.08); box-shadow: 0 8px 24px rgba(13,148,136,0.45); }
    
    /* Window Shell */
    .bot-window {
        position: fixed; bottom: 96px; right: 28px;
        width: 360px; height: 490px;
        background: #ffffff; border-radius: 16px;
        box-shadow: 0 12px 40px rgba(15,23,42,0.15);
        border: 1px solid #e2e8f0;
        display: flex; flex-direction: column;
        z-index: 1050; overflow: hidden;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    
    /* Header */
    .bot-header {
        background: linear-gradient(135deg, #0d9488, #0f766e);
        color: white; padding: 14px 16px;
        display: flex; align-items: center; gap: 12px;
        flex-shrink: 0;
    }
    .bot-header-avatar {
        width: 36px; height: 36px; border-radius: 50%;
        background: rgba(255,255,255,0.15);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem; position: relative;
    }
    .bot-online-indicator {
        position: absolute; bottom: 0; right: 0;
        width: 8px; height: 8px; border-radius: 50%;
        background: #10b981; border: 2px solid #0d9488;
    }
    .bot-header-name { font-size: 0.88rem; font-weight: 700; line-height: 1.2; }
    .bot-header-status { font-size: 0.72rem; opacity: 0.85; }
    .bot-close-btn {
        background: none; border: none; color: white; opacity: 0.8;
        cursor: pointer; font-size: 0.95rem; margin-left: auto;
        transition: opacity 0.15s;
    }
    .bot-close-btn:hover { opacity: 1; }

    /* Messages Panel */
    .bot-messages {
        flex: 1; overflow-y: auto; padding: 14px;
        display: flex; flex-direction: column; gap: 10px;
        background: #f8fafc;
    }
    .bot-messages::-webkit-scrollbar { width: 4px; }
    .bot-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    .bot-msg { display: flex; max-width: 82%; }
    .bot-msg-them { align-self: flex-start; }
    .bot-msg-me { align-self: flex-end; margin-left: auto; }
    .bot-bubble {
        padding: 9px 13px; border-radius: 14px;
        font-size: 0.85rem; line-height: 1.45; word-break: break-word;
    }
    .bot-msg-them .bot-bubble { background: #ffffff; color: #1e293b; border-bottom-left-radius: 4px; border: 1px solid #e2e8f0; }
    .bot-msg-me .bot-bubble { background: #0d9488; color: #ffffff; border-bottom-right-radius: 4px; }

    /* Quick options tag style */
    .bot-options {
        padding: 10px 14px; display: flex; flex-wrap: wrap; gap: 6px;
        background: #f8fafc; border-top: 1px solid #f1f5f9;
        flex-shrink: 0; max-height: 100px; overflow-y: auto;
    }
    .bot-opt-btn {
        background: #ffffff; border: 1px solid #cbd5e1; border-radius: 16px;
        padding: 5px 12px; font-size: 0.76rem; color: #0f766e; font-weight: 500;
        cursor: pointer; transition: all 0.15s; outline: none;
    }
    .bot-opt-btn:hover { background: #f0fdfa; border-color: #0d9488; transform: translateY(-1px); }

    /* Input section */
    .bot-input-row {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 14px; border-top: 1px solid #e2e8f0;
        background: #ffffff; flex-shrink: 0;
    }
    .bot-input {
        flex: 1; border: 1px solid #cbd5e1; border-radius: 20px;
        padding: 8px 14px; font-size: 0.82rem; outline: none; color: #1e293b;
        transition: border-color 0.15s;
    }
    .bot-input:focus { border-color: #0d9488; }
    .bot-send-btn {
        background: none; border: none; color: #0d9488; font-size: 1.1rem;
        cursor: pointer; padding: 0; line-height: 1; flex-shrink: 0;
        transition: transform 0.1s;
    }
    .bot-send-btn:active { transform: scale(0.9); }

    @media (max-width: 480px) {
        .bot-window { width: calc(100vw - 16px); height: calc(100vh - 100px); right: 8px; bottom: 8px; }
        .bot-fab { right: 8px; bottom: 8px; display: none; }
    }
</style>
