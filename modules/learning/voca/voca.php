<?php
require_once '../../../system/includes/config.php';
require_once '../../../system/includes/header.php';
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
    <div class="bg-card text-card-foreground rounded-lg border">
        <!-- Table Header -->
        <div class="p-6 border-b">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold">Vocabulary List</h3>
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <span id="wordCount">0 words</span>
                    <span>•</span>
                    <span id="pageInfo">Page 1 of 1</span>
                </div>
            </div>
        </div>
        
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="text-left p-4 font-medium">Word</th>
                        <th class="text-left p-4 font-medium">Meaning</th>
                        <th class="text-left p-4 font-medium">Example</th>
                        <th class="text-left p-4 font-medium">Added</th>
                        <th class="text-right p-4 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody id="vocabularyTableBody">
                    <!-- Words will be loaded here dynamically -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="p-6 border-t">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <label for="pageSize" class="text-sm">Show:</label>
                    <select id="pageSize" class="px-2 py-1 border rounded text-sm">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-sm text-muted-foreground">per page</span>
                </div>
                <div class="flex items-center gap-2">
                    <button id="prevPage" onclick="changePage(-1)" class="px-3 py-1 border rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        Previous
                    </button>
                    <div id="pageNumbers" class="flex gap-1">
                        <!-- Page numbers will be generated here -->
                    </div>
                    <button id="nextPage" onclick="changePage(1)" class="px-3 py-1 border rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        </div>
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
let currentPage = 1;
let pageSize = 25;
let filteredVocabulary = [];

async function loadVocabulary() {
    try {
        const response = await fetch('fetch_vocabulary.php');
        const result = await response.json();
        
        if (result.success && result.data) {
            vocabulary = result.data;
        } else {
            vocabulary = [];
            console.error('Failed to load vocabulary:', result.error || 'Unknown error');
        }
        
        displayVocabulary();
        updateStats();
    } catch (error) {
        console.error('Error loading vocabulary:', error);
        vocabulary = [];
        displayVocabulary();
        updateStats();
    }
}

function displayVocabulary() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const sortBy = document.getElementById('sortSelect').value;
    
    filteredVocabulary = vocabulary.filter(word => 
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
    
    // Reset to first page when filtering
    currentPage = 1;
    
    renderTable();
    updatePagination();
    updateStats();
}

function renderTable() {
    const container = document.getElementById('vocabularyTableBody');
    const startIndex = (currentPage - 1) * pageSize;
    const endIndex = startIndex + pageSize;
    const pageData = filteredVocabulary.slice(startIndex, endIndex);
    
    if (pageData.length === 0) {
        container.innerHTML = `
            <tr>
                <td colspan="5" class="p-8 text-center text-muted-foreground">
                    <div class="flex flex-col items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="text-muted-foreground/50">
                            <path d="M2 19V6a2 2 0 0 1 2-2h7v17H4a2 2 0 0 1-2-2Zm18 2h-7V4h7a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2Z"/>
                        </svg>
                        <p class="text-lg font-medium">No words found</p>
                        <p class="text-sm">Try adjusting your search or add a new word</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    container.innerHTML = pageData.map(word => `
        <tr class="border-b hover:bg-muted/50 transition-colors">
            <td class="p-4 font-medium">${escapeHtml(word.word)}</td>
            <td class="p-4 text-muted-foreground">${escapeHtml(word.meaning)}</td>
            <td class="p-4 text-sm italic">${word.example ? `"${escapeHtml(word.example)}"` : '-'}</td>
            <td class="p-4 text-sm text-muted-foreground">${new Date(word.created_at).toLocaleDateString()}</td>
            <td class="p-4 text-right">
                <div class="flex gap-2 justify-end">
                    <button onclick="editWord(${word.id})" class="p-2 hover:bg-accent rounded-lg transition-colors" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                            <path d="m15 5 4 4"/>
                        </svg>
                    </button>
                    <button onclick="deleteWord(${word.id})" class="p-2 hover:bg-destructive/10 rounded-lg transition-colors" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18"/>
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function updatePagination() {
    const totalPages = Math.ceil(filteredVocabulary.length / pageSize);
    const startIndex = (currentPage - 1) * pageSize + 1;
    const endIndex = Math.min(currentPage * pageSize, filteredVocabulary.length);
    
    // Update page info
    document.getElementById('wordCount').textContent = `${filteredVocabulary.length} words`;
    document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;
    
    // Update pagination buttons
    document.getElementById('prevPage').disabled = currentPage <= 1;
    document.getElementById('nextPage').disabled = currentPage >= totalPages;
    
    // Generate page numbers
    const pageNumbersContainer = document.getElementById('pageNumbers');
    pageNumbersContainer.innerHTML = '';
    
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const button = document.createElement('button');
        button.textContent = i;
        button.className = `px-3 py-1 border rounded text-sm ${i === currentPage ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'}`;
        button.onclick = () => goToPage(i);
        pageNumbersContainer.appendChild(button);
    }
}

function changePage(delta) {
    const totalPages = Math.ceil(filteredVocabulary.length / pageSize);
    const newPage = currentPage + delta;
    
    if (newPage >= 1 && newPage <= totalPages) {
        currentPage = newPage;
        renderTable();
        updatePagination();
    }
}

function goToPage(page) {
    currentPage = page;
    renderTable();
    updatePagination();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
document.getElementById('pageSize').addEventListener('change', function() {
    pageSize = parseInt(this.value);
    currentPage = 1;
    renderTable();
    updatePagination();
});
document.getElementById('addWordForm').addEventListener('submit', addWord);

// Load vocabulary when page loads
loadVocabulary();
document.getElementById('loadingSpinner').style.display = 'none';
</script>

<?php require_once '../../../system/includes/footer.php'; ?> 