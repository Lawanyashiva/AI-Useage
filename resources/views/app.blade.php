<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AI Notes Manager</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f5f7; color: #1f2937; }
        .app-shell { max-width: 1000px; margin: 0 auto; padding: 24px; }
        .header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; }
        .card { background: #ffffff; border-radius: 16px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06); margin-top: 20px; padding: 20px; }
        .note-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; }
        .button { background: #2563eb; border: none; color: #fff; padding: 12px 18px; border-radius: 10px; cursor: pointer; font-weight: 600; }
        .button.secondary { background: #4b5563; }
        .input, .textarea { width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #d1d5db; margin-top: 10px; }
        .note { border: 1px solid #e5e7eb; padding: 18px; border-radius: 16px; display: flex; flex-direction: column; gap: 12px; }
        .note-title { font-weight: 700; }
        .note-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .flex { display: flex; gap: 12px; flex-wrap: wrap; }
        @media (max-width: 640px) {
            .header { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <div class="header">
        <div>
            <h1>AI Notes Manager</h1>
            <p>Create, search and summarize notes with AI-powered APIs.</p>
        </div>
        <button class="button" onclick="resetForm()">New Note</button>
    </div>

    <div class="card">
        <h2>Search Notes</h2>
        <div class="flex">
            <input id="searchQuery" class="input" placeholder="Search by meaning or keyword" />
            <button class="button" onclick="searchNotes()">Search</button>
        </div>
    </div>

    <div class="card">
        <h2 id="formTitle">Create Note</h2>
        <input id="noteTitle" class="input" placeholder="Title" />
        <textarea id="noteBody" class="textarea" rows="5" placeholder="Write your note here..."></textarea>
        <div class="note-actions">
            <button class="button" onclick="saveNote()">Save</button>
            <button class="button secondary" onclick="resetForm()">Reset</button>
        </div>
    </div>

    <div class="card">
        <h2>Notes</h2>
        <div id="notesList" class="note-grid"></div>
    </div>
</div>

<script>
let currentNoteId = null;

const apiBase = '/api/notes';

async function refreshNotes() {
    const response = await fetch(apiBase + '?limit=20');
    const data = await response.json();
    renderNotes(data.data || data);
}

function renderNotes(notes) {
    const container = document.getElementById('notesList');
    container.innerHTML = notes.map(note => `
        <div class="note">
            <div>
                <div class="note-title">${escapeHtml(note.title)}</div>
                <div>${escapeHtml(note.body.substring(0, 120))}${note.body.length > 120 ? '...' : ''}</div>
            </div>
            <div class="note-actions">
                <button class="button secondary" onclick="editNote(${note.id})">Edit</button>
                <button class="button secondary" onclick="deleteNote(${note.id})">Delete</button>
                <button class="button" onclick="summarizeNote(${note.id})">AI Summary</button>
            </div>
        </div>
    `).join('');
}

function escapeHtml(text) {
    return text.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]));
}

function resetForm() {
    currentNoteId = null;
    document.getElementById('formTitle').textContent = 'Create Note';
    document.getElementById('noteTitle').value = '';
    document.getElementById('noteBody').value = '';
}

async function saveNote() {
    const title = document.getElementById('noteTitle').value.trim();
    const body = document.getElementById('noteBody').value.trim();
    if (!title || !body) {
        alert('Title and body are required.');
        return;
    }
    const method = currentNoteId ? 'PUT' : 'POST';
    const url = currentNoteId ? `${apiBase}/${currentNoteId}` : apiBase;
    await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title, body })
    });
    resetForm();
    refreshNotes();
}

async function editNote(id) {
    const res = await fetch(`${apiBase}/${id}`);
    const note = await res.json();
    currentNoteId = note.id;
    document.getElementById('formTitle').textContent = 'Edit Note';
    document.getElementById('noteTitle').value = note.title;
    document.getElementById('noteBody').value = note.body;
}

async function deleteNote(id) {
    if (!confirm('Delete this note?')) return;
    await fetch(`${apiBase}/${id}`, { method: 'DELETE' });
    refreshNotes();
}

async function summarizeNote(id) {
    const res = await fetch(`${apiBase}/${id}/summary`, { method: 'POST' });
    const data = await res.json();
    alert('Summary:\n' + (data.summary || 'No summary available'));
}

async function searchNotes() {
    const query = document.getElementById('searchQuery').value.trim();
    if (!query) {
        refreshNotes();
        return;
    }
    const res = await fetch(`${apiBase}/search?query=${encodeURIComponent(query)}`);
    const body = await res.json();
    renderNotes(body.data || []);
}

refreshNotes();
</script>
</body>
</html>
