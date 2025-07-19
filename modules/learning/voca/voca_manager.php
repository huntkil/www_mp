<?php
session_start();
$pageTitle = "Vocabulary Manager";
require_once "../../../system/includes/header.php";
?>

<div class="container mx-auto px-4 py-8">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-card text-card-foreground rounded-lg border p-6 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-muted-foreground text-sm">Total Words</p>
                    <p class="text-3xl font-bold" id="totalWords">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-500">
                        <path d="M2 19V6a2 2 0 0 1 2-2h7v17H4a2 2 0 0 1-2-2Zm18 2h-7V4h7a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2Z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-card text-card-foreground rounded-lg border p-6 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-muted-foreground text-sm">This Week</p>
                    <p class="text-3xl font-bold" id="thisWeek">0</p>
                </div>
                <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green-500">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-card text-card-foreground rounded-lg border p-6 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-muted-foreground text-sm">Learning Streak</p>
                    <p class="text-3xl font-bold" id="streak">0</p>
                </div>
                <div class="w-12 h-12 bg-orange-500/10 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-orange-500">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-card text-card-foreground rounded-lg border p-6 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-muted-foreground text-sm">Mastered</p>
                    <p class="text-3xl font-bold" id="mastered">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-purple-500">
                        <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                        <path d="M6 2v3"/>
                        <path d="M6 15H4.5a2.5 2.5 0 0 0 0 5H6"/>
                        <path d="M6 22v-3"/>
                        <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                        <path d="M18 2v3"/>
                        <path d="M18 15h1.5a2.5 2.5 0 0 1 0 5H18"/>
                        <path d="M18 22v-3"/>
                        <path d="M12 2v20"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-card text-card-foreground rounded-lg border p-6 mb-8">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" id="searchInput" placeholder="Search words..." 
                           class="w-full pl-10 pr-4 py-3 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
            </div>
            <div class="flex gap-2">
                <select id="sortSelect" class="px-4 py-3 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="alphabetical">A-Z</option>
                    <option value="reverse">Z-A</option>
                </select>
                <button onclick="showAddWordModal()" class="px-6 py-3 bg-primary text-primary-foreground hover:bg-primary/90 rounded-lg transition-colors flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2">
                        <path d="M5 12h14"/>
                        <path d="M12 5v14"/>
                    </svg>
                    Add Word
                </button>
            </div>
        </div>
    </div>

    <!-- Vocabulary List -->
    <div id="vocabularyList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Words will be loaded here dynamically -->
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>
</div>

<!-- Add Word Modal -->
<div id="addWordModal" class="fixed inset-0 bg-background/80 backdrop-blur-sm z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-card text-card-foreground rounded-lg border p-6 w-full max-w-md">
            <h2 class="text-xl font-semibold mb-4">Add New Word</h2>
            <form id="addWordForm" class="space-y-4">
                <div>
                    <label for="newWord" class="block text-sm font-medium mb-2">Word</label>
                    <input type="text" id="newWord" required class="w-full px-3 py-2 rounded-md border bg-background">
                </div>
                <div>
                    <label for="newMeaning" class="block text-sm font-medium mb-2">Meaning</label>
                    <input type="text" id="newMeaning" required class="w-full px-3 py-2 rounded-md border bg-background">
                </div>
                <div>
                    <label for="newExample" class="block text-sm font-medium mb-2">Example</label>
                    <textarea id="newExample" rows="3" class="w-full px-3 py-2 rounded-md border bg-background"></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-primary text-primary-foreground hover:bg-primary/90 px-4 py-2 rounded-md">
                        Add Word
                    </button>
                    <button type="button" onclick="hideAddWordModal()" class="flex-1 bg-secondary text-secondary-foreground hover:bg-secondary/90 px-4 py-2 rounded-md">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Vocabulary management functionality
let vocabulary = [];

async function loadVocabulary() {
    try {
        const response = await fetch('fetch_vocabulary.php');
        vocabulary = await response.json();
        displayVocabulary();
        updateStats();
    } catch (error) {
        console.error('Error loading vocabulary:', error);
    }
}

function displayVocabulary() {
    const container = document.getElementById('vocabularyList');
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const sortBy = document.getElementById('sortSelect').value;
    
    let filteredVocabulary = vocabulary.filter(word => 
        word.word.toLowerCase().includes(searchTerm) || 
        word.meaning.toLowerCase().includes(searchTerm)
    );
    
    // Sort vocabulary
    switch(sortBy) {
        case 'newest':
            filteredVocabulary.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            break;
        case 'oldest':
            filteredVocabulary.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            break;
        case 'alphabetical':
            filteredVocabulary.sort((a, b) => a.word.localeCompare(b.word));
            break;
        case 'reverse':
            filteredVocabulary.sort((a, b) => b.word.localeCompare(a.word));
            break;
    }
    
    container.innerHTML = filteredVocabulary.map(word => `
        <div class="bg-card text-card-foreground rounded-lg border p-6 hover:shadow-lg transition-all">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-semibold">${word.word}</h3>
                <div class="flex gap-2">
                    <button onclick="editWord(${word.id})" class="p-2 hover:bg-accent rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                            <path d="m15 5 4 4"/>
                        </svg>
                    </button>
                    <button onclick="deleteWord(${word.id})" class="p-2 hover:bg-destructive/10 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18"/>
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                        </svg>
                    </button>
                </div>
            </div>
            <p class="text-muted-foreground mb-2">${word.meaning}</p>
            ${word.example ? `<p class="text-sm italic">"${word.example}"</p>` : ''}
            <div class="mt-4 text-xs text-muted-foreground">
                Added: ${new Date(word.created_at).toLocaleDateString()}
            </div>
        </div>
    `).join('');
    
    document.getElementById('loadingSpinner').style.display = 'none';
}

function updateStats() {
    document.getElementById('totalWords').textContent = vocabulary.length;
    // Add more stats calculation as needed
}

function showAddWordModal() {
    document.getElementById('addWordModal').classList.remove('hidden');
}

function hideAddWordModal() {
    document.getElementById('addWordModal').classList.add('hidden');
    document.getElementById('addWordForm').reset();
}

async function addWord(event) {
    event.preventDefault();
    
    const formData = {
        word: document.getElementById('newWord').value,
        meaning: document.getElementById('newMeaning').value,
        example: document.getElementById('newExample').value
    };
    
    try {
        const response = await fetch('save_vocabulary.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        if (response.ok) {
            hideAddWordModal();
            loadVocabulary();
        } else {
            alert('Failed to add word');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while adding the word');
    }
}

async function deleteWord(id) {
    if (!confirm('Are you sure you want to delete this word?')) return;
    
    try {
        const response = await fetch('delete_vocabulary.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        });
        
        if (response.ok) {
            loadVocabulary();
        } else {
            alert('Failed to delete word');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while deleting the word');
    }
}

function editWord(id) {
    window.location.href = `voca_edit.php?id=${id}`;
}

// Event listeners
document.getElementById('searchInput').addEventListener('input', displayVocabulary);
document.getElementById('sortSelect').addEventListener('change', displayVocabulary);
document.getElementById('addWordForm').addEventListener('submit', addWord);

// Load vocabulary when page loads
loadVocabulary();
</script>

<?php require_once "../../../system/includes/footer.php"; ?> 