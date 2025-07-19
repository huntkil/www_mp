<?php
session_start();
$pageTitle = "Edit Vocabulary";
require "../../../system/includes/header.php";
?>

<div class="container mx-auto px-4 py-8">
  <div class="max-w-2xl mx-auto space-y-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-center">Edit Vocabulary</h1>

    <div class="bg-card text-card-foreground rounded-lg border p-6 space-y-6">
      <form id="editForm" class="space-y-4">
        <div class="space-y-2">
          <label for="word" class="text-sm font-medium">Word</label>
          <input type="text" id="word" name="word" required 
                 class="w-full px-3 py-2 rounded-md border bg-background">
        </div>
        <div class="space-y-2">
          <label for="meaning" class="text-sm font-medium">Meaning</label>
          <input type="text" id="meaning" name="meaning" required 
                 class="w-full px-3 py-2 rounded-md border bg-background">
        </div>
        <div class="space-y-2">
          <label for="example" class="text-sm font-medium">Example</label>
          <textarea id="example" name="example" rows="2" required 
                    class="w-full px-3 py-2 rounded-md border bg-background"></textarea>
        </div>
        <div class="flex items-center gap-4">
          <button type="submit" 
                  class="flex-1 bg-primary text-primary-foreground hover:bg-primary/90 px-4 py-2 rounded-md">
            Save Changes
          </button>
          <a href="voca_manager.php" 
             class="flex-1 bg-secondary text-secondary-foreground hover:bg-secondary/90 px-4 py-2 rounded-md text-center">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const urlParams = new URLSearchParams(window.location.search);
const wordId = urlParams.get('id');

async function loadWord() {
  try {
    const response = await fetch(`fetch_vocabulary.php?id=${wordId}`);
    const word = await response.json();
    
    document.getElementById('word').value = word.word;
    document.getElementById('meaning').value = word.meaning;
    document.getElementById('example').value = word.example;
  } catch (error) {
    console.error('Error:', error);
    alert('An error occurred while loading the word');
  }
}

document.getElementById('editForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = {
    id: wordId,
    word: document.getElementById('word').value,
    meaning: document.getElementById('meaning').value,
    example: document.getElementById('example').value
  };

  try {
    const response = await fetch('update_vocabulary.php', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData)
    });

    if (response.ok) {
      window.location.href = 'voca_manager.php';
    } else {
      alert('Failed to update word');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('An error occurred while updating the word');
  }
});

// Load word data when page loads
loadWord();
</script>

<?php require "../../../system/includes/footer.php"; ?> 