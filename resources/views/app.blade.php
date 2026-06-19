<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AI Notes Manager</title>
    <style>
        :root {
            --bg: #0f172a;
            --panel: rgba(15, 23, 42, 0.9);
            --card: #ffffff;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --primary: #3b82f6;
            --primary-dark: #1d4ed8;
            --accent: #22c55e;
            --danger: #ef4444;
            --shadow: 0 24px 80px rgba(15, 23, 42, 0.25);
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: radial-gradient(circle at top, rgba(56, 189, 248, 0.12), transparent 28%), linear-gradient(180deg, #020617 0%, #0f172a 100%); color: var(--text); }
        body::before { content: ''; position: fixed; inset: 0; background: linear-gradient(135deg, rgba(59,130,246,0.14), rgba(16,185,129,0.1)); pointer-events: none; }
        .app-shell { position: relative; max-width: 1180px; margin: 0 auto; padding: 28px 20px 40px; }
        .topbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
        .brand { max-width: 640px; }
        .brand h1 { margin: 0 0 10px; font-size: clamp(2rem, 3vw, 3.2rem); line-height: 1.05; }
        .brand p { margin: 0; color: var(--muted); font-size: 1rem; max-width: 68ch; }
        .topbar button { min-width: 160px; }
        .grid { display: grid; grid-template-columns: 1.05fr 1.5fr; gap: 24px; }
        .panel { background: rgba(15, 23, 42, 0.88); border: 1px solid rgba(148, 163, 184, 0.12); border-radius: 24px; box-shadow: var(--shadow); padding: 24px; backdrop-filter: blur(14px); }
        .panel h2 { margin-top: 0; color: #fff; }
        .panel small { color: var(--muted); }
        .search-row { display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-top: 18px; }
        .input, .textarea { width: 100%; border-radius: 18px; border: 1px solid rgba(148, 163, 184, 0.2); padding: 16px 18px; background: rgba(255,255,255,0.08); color: #f8fafc; font-size: 0.98rem; }
        .textarea { min-height: 140px; resize: vertical; }
        .button { display: inline-flex; align-items: center; justify-content: center; border: none; border-radius: 16px; padding: 14px 20px; font-weight: 700; cursor: pointer; transition: transform 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease; }
        .button:hover { transform: translateY(-1px); }
        .button.primary { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: #fff; box-shadow: 0 16px 32px rgba(59, 130, 246, 0.24); }
        .button.secondary { background: rgba(255,255,255,0.08); color: #d1d5db; }
        .button.danger { background: rgba(239, 68, 68, 0.96); color: #fff; }
        .status { margin-top: 12px; color: var(--muted); min-height: 22px; }
        .note-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-top: 8px; }
        .note-card { background: #020617; border: 1px solid rgba(148, 163, 184, 0.14); border-radius: 22px; padding: 22px; display: flex; flex-direction: column; gap: 16px; }
        .note-card:hover { transform: translateY(-2px); box-shadow: 0 16px 40px rgba(15, 23, 42, 0.18); }
        .note-title { margin: 0 0 8px; font-size: 1.1rem; color: #fff; }
        .note-body { margin: 0; color: #cbd5e1; line-height: 1.7; }
        .note-meta { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; color: var(--muted); font-size: 0.92rem; }
        .note-actions { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
        .note-actions button { min-width: 0; }
        .footer { margin-top: 18px; color: var(--muted); font-size: 0.95rem; }
        .modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.78); display: none; place-items: center; padding: 24px; z-index: 40; }
        .modal-backdrop.visible { display: grid; }
        .modal { background: #0f172a; border-radius: 24px; padding: 28px; max-width: 600px; width: 100%; box-shadow: var(--shadow); border: 1px solid rgba(148, 163, 184, 0.18); }
        .modal h3 { margin-top: 0; color: #fff; }
        .modal p { color: #cbd5e1; line-height: 1.7; }
        .close-modal { margin-top: 20px; }
        @media (max-width: 960px) { .grid { grid-template-columns: 1fr; } }
        @media (max-width: 720px) { .topbar { flex-direction: column; align-items: stretch; } .search-row { grid-template-columns: 1fr; } .button { width: 100%; } }
    </style>
</head>
<body>
<div class="app-shell">
    <div class="topbar">
        <div class="brand">
            <h1>AI Notes Manager</h1>
            <p>Organize ideas, search by meaning, and generate summaries with AI. Everything is saved through the backend notes API.</p>
        </div>
        <button class="button primary" onclick="resetForm()">New note</button>
    </div>

    <div class="grid">
        <section class="panel">
            <h2>Quick actions</h2>
            <p><small>Search notes or create and edit a note in one place.</small></p>

            <div class="search-row">
                <input id="searchQuery" class="input" placeholder="Search by concept, keyword, or topic" />
                <button class="button secondary" onclick="searchNotes()">Search</button>
            </div>

            <div style="margin-top: 28px;">
                <h2 id="formTitle">Create note</h2>
                <input id="noteTitle" class="input" placeholder="Title" />
                <textarea id="noteBody" class="textarea" placeholder="Write your note here..."></textarea>
                <div class="note-actions" style="margin-top: 14px;">
                    <button class="button primary" onclick="saveNote()">Save note</button>
                    <button class="button secondary" onclick="resetForm()">Reset</button>
                </div>
                <div id="statusMessage" class="status">Ready to manage your notes.</div>
            </div>
        </section>

        <section class="panel">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
                <div>
                    <h2>Notes</h2>
                    <p class="footer">Loaded notes are shown below. Tap a note to edit, delete, or summarize it.</p>
                </div>
                <div id="notesCount" style="color: var(--muted); font-size: 0.95rem;"></div>
            </div>
            <div id="notesList" class="note-grid"></div>
        </section>
    </div>
</div>

<div id="summaryModal" class="modal-backdrop" onclick="closeSummary(event)">
    <div class="modal" role="dialog" aria-modal="true" onclick="event.stopPropagation();">
        <h3>AI Summary</h3>
        <p id="summaryText">Loading summary...</p>
        <button class="button primary close-modal" onclick="closeSummary()">Close</button>
    </div>
</div>

<script>
let currentNoteId = null;
const apiBase = '/api/notes';
const statusMessage = document.getElementById('statusMessage');
const notesCount = document.getElementById('notesCount');
const summaryModal = document.getElementById('summaryModal');
const summaryText = document.getElementById('summaryText');

function setStatus(message, isError = false) {
    statusMessage.textContent = message;
    statusMessage.style.color = isError ? '#fecaca' : '#94a3b8';
}

async function refreshNotes() {
    try {
        const response = await fetch(apiBase + '?limit=20');
        if (!response.ok) throw new Error('Unable to load notes');
        const data = await response.json();
        const notes = data.data || data;
        renderNotes(notes);
        notesCount.textContent = `${notes.length} note${notes.length === 1 ? '' : 's'}`;
        setStatus('Notes refreshed.');
    } catch (error) {
        setStatus('Failed to load notes. Check the backend.', true);
        document.getElementById('notesList').innerHTML = '<div class="note-card"><p style="margin:0; color:#cbd5e1;">Unable to fetch notes.</p></div>';
    }
}

function renderNotes(notes) {
    const container = document.getElementById('notesList');
    if (!Array.isArray(notes) || notes.length === 0) {
        container.innerHTML = '<div class="note-card"><p style="margin:0; color:#cbd5e1;">No notes yet. Add one to begin.</p></div>';
        return;
    }
    container.innerHTML = notes.map(note => `
        <article class="note-card">
            <div>
                <h3 class="note-title">${escapeHtml(note.title)}</h3>
                <p class="note-body">${escapeHtml(note.body.substring(0, 140))}${note.body.length > 140 ? '...' : ''}</p>
            </div>
            <div class="note-meta">
                ${note.updated_at ? `<span>Updated ${new Date(note.updated_at).toLocaleDateString()}</span>` : ''}
                <span>${note.body.split(' ').length} words</span>
            </div>
            <div class="note-actions">
                <button class="button secondary" onclick="editNote(${note.id})">Edit</button>
                <button class="button danger" onclick="deleteNote(${note.id})">Delete</button>
                <button class="button primary" onclick="summarizeNote(${note.id})">Summary</button>
            </div>
        </article>
    `).join('');
}

function escapeHtml(text = '') {
    return String(text).replace(/[&<>"']/g, c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c]));
}

function resetForm() {
    currentNoteId = null;
    document.getElementById('formTitle').textContent = 'Create note';
    document.getElementById('noteTitle').value = '';
    document.getElementById('noteBody').value = '';
    setStatus('Ready to create a new note.');
}

async function saveNote() {
    const title = document.getElementById('noteTitle').value.trim();
    const body = document.getElementById('noteBody').value.trim();
    if (!title || !body) {
        setStatus('Title and body are required.', true);
        return;
    }
    const method = currentNoteId ? 'PUT' : 'POST';
    const url = currentNoteId ? `${apiBase}/${currentNoteId}` : apiBase;
    try {
        const response = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, body })
        });
        if (!response.ok) {
            const errorBody = await response.json();
            setStatus(errorBody.message || 'Unable to save note.', true);
            return;
        }
        resetForm();
        await refreshNotes();
        setStatus(currentNoteId ? 'Note updated.' : 'Note created successfully.');
    } catch (error) {
        setStatus('Failed to save note. Backend may be offline.', true);
    }
}

async function editNote(id) {
    try {
        const res = await fetch(`${apiBase}/${id}`);
        if (!res.ok) throw new Error('Unable to load note');
        const note = await res.json();
        currentNoteId = note.id;
        document.getElementById('formTitle').textContent = 'Edit note';
        document.getElementById('noteTitle').value = note.title;
        document.getElementById('noteBody').value = note.body;
        setStatus('Editing note #' + id);
    } catch (error) {
        setStatus('Unable to load note for editing.', true);
    }
}

async function deleteNote(id) {
    if (!confirm('Delete this note?')) return;
    try {
        const res = await fetch(`${apiBase}/${id}`, { method: 'DELETE' });
        if (!res.ok) throw new Error('Delete failed');
        await refreshNotes();
        setStatus('Note deleted.');
    } catch (error) {
        setStatus('Unable to delete note.', true);
    }
}

async function summarizeNote(id) {
    try {
        summaryText.textContent = 'Generating summary...';
        summaryModal.classList.add('visible');
        const res = await fetch(`${apiBase}/${id}/summary`, { method: 'POST' });
        if (!res.ok) throw new Error('Summary failed');
        const data = await res.json();
        summaryText.textContent = data.summary || 'No summary available.';
    } catch (error) {
        summaryText.textContent = 'Unable to generate summary. Please try again.';
    }
}

function closeSummary(event) {
    if (event && event.target !== summaryModal) return;
    summaryModal.classList.remove('visible');
}

async function searchNotes() {
    const query = document.getElementById('searchQuery').value.trim();
    if (!query) {
        await refreshNotes();
        return;
    }
    try {
        const res = await fetch(`${apiBase}/search?query=${encodeURIComponent(query)}`);
        if (!res.ok) throw new Error('Search failed');
        const body = await res.json();
        const notes = body.data || [];
        renderNotes(notes);
        notesCount.textContent = `${notes.length} result${notes.length === 1 ? '' : 's'}`;
        setStatus(`Showing results for “${query}”.`);
    } catch (error) {
        setStatus('Unable to search notes.', true);
    }
}

refreshNotes();
</script>
</body>
</html>
